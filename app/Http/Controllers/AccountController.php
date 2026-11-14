<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\AppSetting;
use App\Models\PaymentCategory;
use App\Models\Trade;
use App\Models\PaymentMethod;
use App\Models\Tournament;
use App\Models\PromoCode;
use App\Models\User;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    private function ensureAuth()
    {
        if (!Auth::check()) {
            abort(redirect()->route('login'));
        }
        return Auth::user();
    }

    public function deposit()
    {
        $user = $this->ensureAuth();

        Transaction::where('user_id', $user->id)
            ->where('type', 'deposit')
            ->where('provider', 'lgpay')
            ->whereIn('status', ['pending', 'processing'])
            ->where('created_at', '<', now()->subMinutes(30))
            ->update([
                'status' => 'rejected',
                'provider_status' => 'expired_without_confirmation',
                'processed_at' => now(),
                'notes' => 'Online auto payment expired without gateway confirmation.',
                'updated_at' => now(),
            ]);

        $hasPaymentCategories = Schema::hasTable('payment_categories') && Schema::hasTable('payment_category_method');
        $methods = Schema::hasTable('payment_methods')
            ? PaymentMethod::active()
                ->deposits()
                ->when($hasPaymentCategories, fn ($query) => $query->with('categories'))
                ->get()
            : collect([]);

        $lgPayEnabled = Schema::hasTable('app_settings') && AppSetting::get('lgpay.enabled', '0') === '1';
        $lgPayMethods = $lgPayEnabled
            ? $methods->where('provider', 'lgpay')->sortBy('sort_order')->values()
            : collect([]);
        $methods = $methods->reject(fn ($method) => $method->provider === 'lgpay')->values();
        $visibleDepositMethods = $methods->concat($lgPayMethods)->keyBy('id');

        $paymentCategories = collect();
        if ($hasPaymentCategories) {
            $paymentCategories = PaymentCategory::active()
                ->shownOnDeposit()
                ->with(['paymentMethods' => function ($query) {
                    $query->active()->deposits();
                }])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map(function (PaymentCategory $category) use ($visibleDepositMethods) {
                    $category->setRelation(
                        'paymentMethods',
                        $category->paymentMethods
                            ->filter(fn ($method) => $visibleDepositMethods->has($method->id))
                            ->values()
                    );

                    return $category;
                })
                ->filter(fn (PaymentCategory $category) => $category->paymentMethods->isNotEmpty())
                ->values();
        }

        if ($paymentCategories->isEmpty() && $visibleDepositMethods->isNotEmpty()) {
            $fallbackCategory = new PaymentCategory([
                'name' => 'Available',
                'slug' => 'available',
                'icon' => 'card',
                'short_label' => 'Available payment methods',
                'sort_order' => 0,
                'is_active' => true,
                'show_on_deposit' => true,
            ]);
            $fallbackCategory->id = 0;
            $fallbackCategory->setRelation('paymentMethods', $visibleDepositMethods->values());
            $paymentCategories = collect([$fallbackCategory]);
        }

        $recentDeposits = Transaction::where('user_id', $user->id)
            ->where('type', 'deposit')
            ->latest()
            ->limit(5)
            ->get();

        return view('account.deposit', compact('user', 'methods', 'lgPayMethods', 'paymentCategories', 'recentDeposits'));
    }

    public function processDeposit(Request $request)
    {
        $request->validate([
            'amount'            => 'required|numeric|min:5',
            'method'            => 'required|string',
            'payment_method_id' => 'required|integer',
            'payment_category_id' => 'nullable|integer',
            'transaction_id'    => 'nullable|string|max:255',
            'proof_image'       => 'nullable|image|mimes:jpeg,png,jpg,webp,heic|max:10240',
        ]);

        // At least one of transaction_id OR proof_image is strictly required
        if (!$request->filled('transaction_id') && !$request->hasFile('proof_image')) {
            return back()->withInput()->withErrors([
                'proof_error' => 'Please provide either a Transaction ID or upload a payment screenshot/receipt (or both).'
            ]);
        }

        $user = $this->ensureAuth();
        $amount = (float) $request->amount;

        if (!Schema::hasTable('payment_methods')) {
            return back()->withInput()->withErrors(['method' => 'Payment methods are not configured.']);
        }

        $paymentMethodQuery = PaymentMethod::active()
            ->deposits()
            ->where('id', $request->integer('payment_method_id'));

        if (Schema::hasColumn('payment_methods', 'provider')) {
            $paymentMethodQuery->where(function ($query) {
                $query->where('provider', 'manual')->orWhereNull('provider');
            });
        }

        $paymentMethod = $paymentMethodQuery->first();

        if (!$paymentMethod) {
            return back()->withInput()->withErrors(['method' => 'Selected payment method is unavailable.']);
        }

        if ($request->integer('payment_category_id') > 0 && Schema::hasTable('payment_categories') && Schema::hasTable('payment_category_method')) {
            $categoryIsValid = PaymentCategory::active()
                ->shownOnDeposit()
                ->whereKey($request->integer('payment_category_id'))
                ->whereHas('paymentMethods', function ($query) use ($paymentMethod) {
                    $query->where('payment_methods.id', $paymentMethod->id)
                        ->where('payment_methods.is_active', true)
                        ->whereIn('payment_methods.type', ['deposit', 'both']);
                })
                ->exists();

            if (!$categoryIsValid) {
                return back()->withInput()->withErrors(['method' => 'Selected payment category and method are unavailable.']);
            }
        }

        if ($amount < (float) $paymentMethod->min_amount || $amount > (float) $paymentMethod->max_amount) {
            return back()->withInput()->withErrors(['amount' => 'Amount is outside the allowed limit for this method.']);
        }

        $currency = $paymentMethod->currency;
        $conversionRate = (float) $paymentMethod->conversion_rate;
        $convertedAmount = round($amount * $conversionRate, 2);

        // Upload proof image if provided
        $proofPath = null;
        if ($request->hasFile('proof_image')) {
            $uploadDir = public_path('uploads/deposits');
            if (!File::exists($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true);
            }
            $file = $request->file('proof_image');
            $filename = 'deposit_' . $user->id . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $proofPath = 'uploads/deposits/' . $filename;
        }

        Transaction::create([
            'user_id'           => $user->id,
            'account_type'      => 'live',
            'type'              => 'deposit',
            'amount'            => $amount,
            'converted_amount'  => $convertedAmount,
            'currency'          => $currency,
            'method'            => $paymentMethod->name,
            'payment_method_id' => $paymentMethod->id,
            'transaction_id'    => $request->transaction_id,
            'proof_image'       => $proofPath,
            'wallet_address'    => $paymentMethod->wallet_address,
            'status'            => 'pending',
            'notes'             => 'Awaiting admin verification.',
        ]);

        return redirect()->route('deposit')->with('success', 'Deposit request of $' . number_format($amount, 2) . ' submitted successfully! It is pending admin review and will be added to your balance once verified.');
    }

    public function withdrawal()
    {
        $user = $this->ensureAuth();
        $isVerified = $user->isKycVerified();

        $methods = collect([]);
        if (Schema::hasTable('payment_methods')) {
            $methods = PaymentMethod::active()->withdrawals()->get();
            if ($methods->isEmpty()) {
                $methods = PaymentMethod::active()->get();
            }
        }

        $recentTransactions = Transaction::where('user_id', $user->id)
            ->where('type', 'withdrawal')
            ->latest()
            ->limit(10)
            ->get();

        return view('account.withdrawal', compact('user', 'methods', 'recentTransactions', 'isVerified'));
    }

    public function processWithdrawal(Request $request)
    {
        $this->ensureAuth();

        $validated = $request->validate([
            'amount'       => 'required|numeric|min:10',
            'method'       => 'required|string',
            'purse'        => 'nullable|string|max:255',
            'destination'  => 'nullable|string|max:255',
            'first_name'   => 'nullable|string|max:100',
            'last_name'    => 'nullable|string|max:100',
            'receive_type' => 'nullable|string|max:100',
        ]);

        $destination = $request->purse ?: $request->destination;
        if (empty($destination)) {
            return back()->withErrors(['purse' => 'Please provide destination account/purse number.']);
        }

        $amount = $validated['amount'];
        $notes = 'Withdrawal via ' . $validated['method'];
        if ($request->filled('first_name') || $request->filled('last_name')) {
            $notes .= ' | Name: ' . trim($request->first_name . ' ' . $request->last_name);
        }
        if ($request->filled('receive_type')) {
            $notes .= ' | Type: ' . $request->receive_type;
        }

        $created = DB::transaction(function () use ($amount, $destination, $notes, $validated) {
            $user = User::whereKey(Auth::id())->lockForUpdate()->firstOrFail();

            if (Money::greaterThan($amount, $user->live_balance ?? 0)) {
                return null;
            }

            $user->live_balance = Money::subtract($user->live_balance, $amount);
            $user->save();

            return Transaction::create([
                'user_id'        => $user->id,
                'account_type'   => 'live',
                'type'           => 'withdrawal',
                'amount'         => $amount,
                'method'         => $validated['method'],
                'wallet_address' => $destination,
                'status'         => 'pending',
                'notes'          => $notes,
            ]);
        });

        if (!$created) {
            $user = Auth::user()->fresh();
            return back()->withErrors(['amount' => 'Insufficient live balance. You currently have $' . number_format((float) ($user->live_balance ?? 0), 2) . ' available.']);
        }

        return redirect()->route('withdrawal')->with('success', 'Withdrawal request of $' . number_format($amount, 2) . ' submitted! It will be sent after admin approval.');
    }

    public function history(Request $request)
    {
        $user = $this->ensureAuth();
        TradeController::settleExpiredTradesForUser($user);

        $accountType = $request->query('account', 'demo');
        $startDate   = $request->query('startDate');
        $endDate     = $request->query('endDate');

        // If user has 0 trades, seed initial realistic trades matching Quotex DOM
        if (Trade::where('user_id', $user->id)->count() === 0) {
            $asset = \App\Models\Asset::where('symbol', 'USD/JPY (OTC)')->first() 
                ?? \App\Models\Asset::firstOrCreate(
                    ['symbol' => 'USD/JPY (OTC)'],
                    ['name' => 'USD/JPY (OTC)', 'type' => 'currency', 'payout_rate' => 92, 'is_active' => true, 'current_price' => 154.085]
                );

            $demoTrades = [
                ['dir' => 'up', 'inv' => 100, 'open' => 154.076, 'close' => 154.098, 'res' => 'win',  'payout' => 192.00, 'sec' => 5],
                ['dir' => 'up', 'inv' => 100, 'open' => 154.076, 'close' => 154.098, 'res' => 'win',  'payout' => 192.00, 'sec' => 5],
                ['dir' => 'up', 'inv' => 100, 'open' => 154.073, 'close' => 154.093, 'res' => 'win',  'payout' => 192.00, 'sec' => 5],
                ['dir' => 'up', 'inv' => 100, 'open' => 154.073, 'close' => 154.093, 'res' => 'win',  'payout' => 192.00, 'sec' => 5],
                ['dir' => 'up', 'inv' => 100, 'open' => 154.073, 'close' => 154.093, 'res' => 'win',  'payout' => 192.00, 'sec' => 5],
                ['dir' => 'down', 'inv' => 100, 'open' => 154.073, 'close' => 154.084, 'res' => 'loss', 'payout' => 0.00,   'sec' => 5],
                ['dir' => 'down', 'inv' => 100, 'open' => 154.073, 'close' => 154.084, 'res' => 'loss', 'payout' => 0.00,   'sec' => 5],
                ['dir' => 'down', 'inv' => 100, 'open' => 154.073, 'close' => 154.084, 'res' => 'loss', 'payout' => 0.00,   'sec' => 5],
                ['dir' => 'up', 'inv' => 100, 'open' => 154.069, 'close' => 154.082, 'res' => 'win',  'payout' => 192.00, 'sec' => 5],
                ['dir' => 'up', 'inv' => 100, 'open' => 154.069, 'close' => 154.082, 'res' => 'win',  'payout' => 192.00, 'sec' => 5],
                ['dir' => 'up', 'inv' => 100, 'open' => 154.069, 'close' => 154.082, 'res' => 'win',  'payout' => 192.00, 'sec' => 5],
                ['dir' => 'up', 'inv' => 100, 'open' => 154.069, 'close' => 154.082, 'res' => 'win',  'payout' => 192.00, 'sec' => 5],
                ['dir' => 'down', 'inv' => 100, 'open' => 154.065, 'close' => 154.079, 'res' => 'loss', 'payout' => 0.00,   'sec' => 5],
                ['dir' => 'down', 'inv' => 100, 'open' => 154.065, 'close' => 154.079, 'res' => 'loss', 'payout' => 0.00,   'sec' => 5],
                ['dir' => 'down', 'inv' => 100, 'open' => 154.065, 'close' => 154.079, 'res' => 'loss', 'payout' => 0.00,   'sec' => 5],
            ];

            foreach ($demoTrades as $idx => $dt) {
                $openTime = now()->subMinutes(10)->subSeconds($idx * 6);
                Trade::create([
                    'user_id'          => $user->id,
                    'asset_id'         => $asset->id,
                    'direction'        => $dt['dir'],
                    'investment'       => $dt['inv'],
                    'payout'           => $dt['payout'],
                    'open_price'       => $dt['open'],
                    'close_price'      => $dt['close'],
                    'duration_seconds' => $dt['sec'],
                    'result'           => $dt['res'],
                    'account_type'     => 'demo',
                    'opened_at'        => $openTime,
                    'closed_at'        => $openTime->copy()->addSeconds($dt['sec']),
                ]);
            }
        }

        $query = Trade::with('asset')
            ->where('user_id', $user->id)
            ->where('result', '!=', 'pending');

        if (in_array($accountType, ['demo', 'live'])) {
            $query->where('account_type', $accountType);
        }

        if ($startDate) {
            $query->whereDate('opened_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('opened_at', '<=', $endDate);
        }

        if ($request->query('export') === 'csv') {
            $allTrades = $query->latest('opened_at')->get();
            $csvFileName = 'trades_history_' . date('Y-m-d_H-i') . '.csv';
            $headers = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$csvFileName}\"",
            ];
            $callback = function () use ($allTrades) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Asset', 'Payout %', 'Direction', 'Investment', 'Open Price', 'Close Price', 'Open Time', 'Close Time', 'Result', 'Profit']);
                foreach ($allTrades as $t) {
                    fputcsv($file, [
                        $t->asset->symbol ?? 'USD/JPY (OTC)',
                        ($t->asset->payout_rate ?? 92) . '%',
                        strtoupper($t->direction),
                        $t->investment . '$',
                        $t->open_price,
                        $t->close_price,
                        $t->opened_at ? $t->opened_at->format('Y-m-d H:i:s') : '',
                        $t->closed_at ? $t->closed_at->format('Y-m-d H:i:s') : '',
                        strtoupper($t->result),
                        $t->payout . '$'
                    ]);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }

        $trades = $query->latest('opened_at')->paginate(10)->withQueryString();

        return view('account.history', compact('trades', 'user', 'accountType', 'startDate', 'endDate'));
    }

    public function pendingTrades(Request $request)
    {
        $user = $this->ensureAuth();
        TradeController::settleExpiredTradesForUser($user);

        $accountType = $request->query('account', 'demo');

        $trades = Trade::with('asset')
            ->where('user_id', $user->id)
            ->where('result', 'pending')
            ->when(in_array($accountType, ['demo', 'live']), function($q) use ($accountType) {
                $q->where('account_type', $accountType);
            })
            ->latest('opened_at')
            ->paginate(10)
            ->withQueryString();

        return view('account.history', [
            'trades'      => $trades,
            'user'        => $user,
            'accountType' => $accountType,
            'isPending'   => true,
        ]);
    }

    public function profile()
    {
        $user = $this->ensureAuth();
        return view('account.profile', compact('user'));
    }

    public function market()
    {
        $user = $this->ensureAuth();

        if (!Schema::hasTable('promo_codes')) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {
            }
        }

        if (Schema::hasTable('promo_codes') && PromoCode::count() === 0) {
            // Seed initial Quotex promo codes
            PromoCode::create([
                'code'         => 'DEPOSIT50',
                'category'     => 'deposit_bonus',
                'reward_type'  => 'percentage',
                'reward_value' => 50.00,
                'min_deposit'  => 50.00,
                'expires_at'   => '2030-10-29 23:59:59',
                'is_unlimited' => false,
                'is_active'    => true,
                'sort_order'   => 1,
                'description'  => 'Receive a 50% extra bonus on your deposit.',
            ]);

            PromoCode::create([
                'code'         => 'WELCOME50',
                'category'     => 'deposit_bonus',
                'reward_type'  => 'percentage',
                'reward_value' => 50.00,
                'min_deposit'  => 10.00,
                'expires_at'   => null,
                'is_unlimited' => true,
                'is_active'    => true,
                'sort_order'   => 2,
                'description'  => 'Welcome 50% deposit bonus for all traders.',
            ]);

            PromoCode::create([
                'code'         => 'DEPOSIT40',
                'category'     => 'deposit_bonus',
                'reward_type'  => 'percentage',
                'reward_value' => 40.00,
                'min_deposit'  => 30.00,
                'expires_at'   => '2030-10-29 23:59:59',
                'is_unlimited' => false,
                'is_active'    => true,
                'sort_order'   => 3,
                'description'  => 'Receive a 40% extra bonus on your deposit.',
            ]);
        }

        $activePromos = collect([]);
        if (Schema::hasTable('promo_codes')) {
            $activePromos = PromoCode::active()->get()->groupBy('category');
        }

        return view('account.market', compact('user', 'activePromos'));
    }

    public function applyPromo(Request $request)
    {
        $this->ensureAuth();

        $code = strtoupper(trim($request->input('code', '')));
        if (empty($code)) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid promo code.',
            ], 422);
        }

        if (!Schema::hasTable('promo_codes')) {
            return response()->json([
                'success' => false,
                'message' => 'Promo code not found or has expired.',
            ], 404);
        }

        $promo = PromoCode::active()->where('code', $code)->first();
        if (!$promo) {
            return response()->json([
                'success' => false,
                'message' => 'Promo code "' . $code . '" is invalid or has expired.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Promo code "' . $promo->code . '" activated successfully! (' . $promo->formatted_name . ')',
            'redirect' => route('deposit', ['promo' => $promo->code]),
            'promo' => [
                'code' => $promo->code,
                'category' => $promo->category,
                'reward_type' => $promo->reward_type,
                'reward_value' => $promo->reward_value,
                'formatted_name' => $promo->formatted_name,
            ]
        ]);
    }

    public function tournaments()
    {
        $user = $this->ensureAuth();

        $activeTournaments = collect([]);
        $completedTournaments = collect([]);

        if (Schema::hasTable('tournaments')) {
            $activeTournaments = Tournament::active()->get();
            $completedTournaments = Tournament::completed()->get();
        }

        return view('account.tournaments', compact('user', 'activeTournaments', 'completedTournaments'));
    }

    public function tournamentDetail($id)
    {
        $user = $this->ensureAuth();

        if (!Schema::hasTable('tournaments')) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {}
        }

        $tournament = Tournament::find($id);
        if (!$tournament) {
            // Fallback to first tournament or default Crazy Wednesday
            $tournament = Tournament::first() ?? new Tournament([
                'id'                 => 933,
                'title'              => 'Crazy Wednesday',
                'prize_pool'         => 7500.00,
                'entry_fee'          => 10.00,
                'rebuy_cost'         => 10.00,
                'number_of_rebuys'   => 100,
                'duration'           => '1 day',
                'timezone_label'     => 'UTC+05:00',
                'badge_text'         => 'Until start: 1 day(s)',
                'description'        => 'Total prize pool is $7500. To participate you need to register and pay the entrance fee, after that $100 will be deposited to a special tournament account. Initial conditions in the tournament are the same for all participants. During one day, participants need to make trades on the tournament account. And 5 traders with the biggest tournament balance will become winners.',
                'prize_distribution' => [
                    ['place' => '1 Place', 'prize' => '2300$'],
                    ['place' => '2 Place', 'prize' => '1800$'],
                    ['place' => '3 Place', 'prize' => '1500$'],
                    ['place' => '4 Place', 'prize' => '1200$'],
                    ['place' => '5 Place', 'prize' => '700$'],
                ]
            ]);
        }

        return view('account.tournament_detail', compact('user', 'tournament'));
    }

    public function analytics(Request $request)
    {
        $user = $this->ensureAuth();

        $trades = Trade::where('user_id', $user->id)->get();
        $tradesCount = $trades->count();
        $profitableCount = $trades->where('result', 'win')->count();
        $tradesProfit = $trades->where('result', 'win')->sum('profit') - $trades->where('result', 'loss')->sum('amount');
        $netTurnover = $trades->sum('amount');
        $avgProfit = $profitableCount > 0 ? round($trades->where('result', 'win')->avg('profit'), 2) : 0;
        $minTrade = $tradesCount > 0 ? $trades->min('amount') : 0;
        $maxTrade = $tradesCount > 0 ? $trades->max('amount') : 0;
        $maxProfit = $profitableCount > 0 ? $trades->where('result', 'win')->max('profit') : 0;

        $stats = [
            'trades_count'      => $tradesCount,
            'trades_profit'     => $tradesProfit,
            'profitable_trades' => $profitableCount,
            'avg_profit'        => $avgProfit,
            'net_turnover'      => $netTurnover,
            'hedged_trades'     => 0,
            'min_trade'         => $minTrade,
            'max_trade'         => $maxTrade,
            'max_profit'        => $maxProfit,
        ];

        return view('account.analytics', compact('user', 'stats'));
    }

    public function referral(Request $request)
    {
        $user = $this->ensureAuth();
        $user->ensureReferralCode();

        $settings = \App\Models\ReferralSetting::getSettings();
        $referrals = $user->referrals()
            ->withCount([
                'trades',
                'trades as live_trades_count' => fn ($query) => $query->where('account_type', 'live'),
            ])
            ->latest()
            ->get();
        $commissions = $user->commissionsEarned()
            ->with(['referredUser', 'trade'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total_earned'     => $user->getTotalCommissionEarned(),
            'win_earned'       => $user->getWinCommissionEarned(),
            'loss_earned'      => $user->getLossCommissionEarned(),
            'deposit_earned'   => $user->getDepositCommissionEarned(),
            'total_referrals'  => $referrals->count(),
            'active_traders'   => $referrals->where('live_trades_count', '>', 0)->count(),
        ];

        return view('account.referral', compact('user', 'settings', 'referrals', 'commissions', 'stats'));
    }

    public function updateProfile(Request $request)
    {
        $user = $this->ensureAuth();

        $request->validate([
            'name'    => 'nullable|string|max:255',
            'country' => 'nullable|string',
        ]);

        $user->update([
            'name'    => $request->name ?? $user->name,
            'country' => $request->country ?? $user->country,
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6|confirmed']);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }
}
