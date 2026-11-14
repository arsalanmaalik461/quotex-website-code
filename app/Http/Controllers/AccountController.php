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
use App\Services\TradeResultService;
use App\Services\VerificationCodeService;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class AccountController extends Controller
{
    private function ensureAuth()
    {
        if (!Auth::check()) {
            abort(redirect()->route('login'));
        }
        return Auth::user();
    }

    public function deposit(Request $request)
    {
        $user = $this->ensureAuth();

        // A promo code carried in from the Market page (?promo=CODE) or the
        // 50% banner: validate it up front so the page can show the bonus
        // the user will get (or a clear error) before they deposit.
        $appliedPromo = null;
        $promoError = null;
        if ($request->filled('promo')) {
            $resolved = $this->resolveDepositPromo($request->query('promo'));
            $appliedPromo = $resolved['promo'];
            $promoError = $resolved['error'];
        }
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

        return view('account.deposit', compact('user', 'methods', 'lgPayMethods', 'paymentCategories', 'recentDeposits', 'appliedPromo', 'promoError'));
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
            'promo_code'        => 'nullable|string|max:40',
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

        // Promo code (typed on this page or carried in via ?promo=): a code
        // that is invalid, expired, not a deposit bonus, or below its
        // minimum deposit is a hard error — the deposit is not created and
        // nothing is credited.
        $promoBonus = 0.0;
        $appliedPromo = null;
        $promoCodeInput = strtoupper(trim((string) $request->input('promo_code', '')));
        if ($promoCodeInput !== '') {
            $resolved = $this->resolveDepositPromo($promoCodeInput, $amount);
            if ($resolved['error'] !== null) {
                return back()->withInput()->withErrors(['promo_code' => $resolved['error']]);
            }
            $appliedPromo = $resolved['promo'];
            $promoBonus = $resolved['bonus'];
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

        $deposit = Transaction::create([
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

        // Credit the promo bonus to the same (live) account as the deposit
        // and record it as its own 'bonus' transaction. This runs in its
        // own try/catch so a bonus failure (e.g. the 'bonus' transaction
        // type not yet enabled in the DB enum) never blocks the deposit
        // itself; the user is told instead.
        // NOTE: the deposit itself is still pending admin review while the
        // bonus is credited immediately. If the deposit is later rejected
        // the bonus stays in the balance unless an admin reverses the
        // linked bonus row (see its notes for the deposit id). The correct
        // long-term placement is to credit the bonus when the deposit is
        // approved (AdminTransactionController::approveTransaction), which
        // is admin code and out of scope for this change.
        $bonusFailed = false;
        if ($appliedPromo !== null && $promoBonus > 0) {
            try {
                DB::transaction(function () use ($user, $appliedPromo, $promoBonus, $deposit) {
                    $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
                    $lockedUser->live_balance = Money::add($lockedUser->live_balance, $promoBonus);
                    $lockedUser->save();

                    Transaction::create([
                        'user_id'      => $user->id,
                        'account_type' => 'live',
                        'type'         => 'bonus',
                        'amount'       => $promoBonus,
                        'method'       => 'Promo code ' . $appliedPromo->code,
                        'status'       => 'approved',
                        'processed_at' => now(),
                        'notes'        => 'Promo bonus ' . $appliedPromo->code . ' (' . $appliedPromo->formatted_name . ') for deposit #' . $deposit->id . ' of $' . number_format((float) $deposit->amount, 2) . '.',
                    ]);
                });
            } catch (\Throwable $e) {
                $bonusFailed = true;
                Log::error('Promo bonus credit failed for deposit #' . $deposit->id . ': ' . $e->getMessage(), [
                    'user_id'    => $user->id,
                    'promo_code' => $appliedPromo->code,
                    'bonus'      => $promoBonus,
                ]);
            }
        }

        $successMessage = 'Deposit request of $' . number_format($amount, 2) . ' submitted successfully! It is pending admin review and will be added to your balance once verified.';
        if ($appliedPromo !== null && $promoBonus > 0 && !$bonusFailed) {
            $successMessage .= ' Promo bonus of $' . number_format($promoBonus, 2) . ' from code ' . $appliedPromo->code . ' has been credited to your live balance.';
        }

        $redirect = redirect()->route('deposit')->with('success', $successMessage);
        if ($bonusFailed) {
            $redirect->with('warning', 'Your deposit was submitted, but the $' . number_format($promoBonus, 2) . ' bonus from promo code ' . $appliedPromo->code . ' could not be credited due to a system issue. Please contact support and mention promo code ' . $appliedPromo->code . ' and deposit #' . $deposit->id . '.');
        }

        return $redirect;
    }

    public function withdrawal()
    {
        $user = $this->ensureAuth();
        $isVerified = $user->isKycVerified();
        $emailVerified = (bool) ($user->email_verified || $user->email_verified_at);

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

        return view('account.withdrawal', compact('user', 'methods', 'recentTransactions', 'isVerified', 'emailVerified'));
    }

    public function processWithdrawal(Request $request)
    {
        $user = $this->ensureAuth();

        // KYC gate (server-side): no withdrawal until identity is
        // verified. The page also shows a notice, but this is the
        // authoritative check — it cannot be bypassed from the client.
        if (!$user->isKycVerified()) {
            return back()->withInput()->withErrors(['kyc' => 'Withdrawals are available after identity verification. Please complete verification on the KYC page (Verification section in your Profile) first.']);
        }

        // Email gate (server-side): the account email must be verified.
        if (!($user->email_verified || $user->email_verified_at)) {
            return back()->withInput()->withErrors(['email' => 'Please verify your email address before withdrawing. You can resend the verification link from the Resend button on your Profile page.']);
        }

        $validated = $request->validate([
            'amount'            => 'required|numeric|min:10',
            'method'            => 'required|string',
            'purse'             => 'nullable|string|max:255',
            'destination'       => 'nullable|string|max:255',
            'first_name'        => 'nullable|string|max:100',
            'last_name'         => 'nullable|string|max:100',
            'receive_type'      => 'nullable|string|max:100',
            'verification_code' => 'nullable|string|max:10',
        ]);

        // Withdrawal 2FA: when enabled, the same emailed 6-digit code
        // used at sign-in must accompany the request. First submit
        // (no code) issues + emails the code and asks for it; the
        // withdrawal is only created once a correct, unexpired code
        // is submitted with the request.
        if ($user->withdrawal_2fa) {
            if (!$request->filled('verification_code')) {
                $code = VerificationCodeService::issue($user, VerificationCodeService::PURPOSE_WITHDRAWAL);
                if (!VerificationCodeService::sendCodeEmail($user, VerificationCodeService::PURPOSE_WITHDRAWAL, $code)) {
                    return back()->withInput()->withErrors(['verification_code' => 'We could not send the confirmation code to your email. Please try again.']);
                }

                return back()->withInput()->with('withdrawal_code_required', true);
            }

            if (!VerificationCodeService::verify($user, VerificationCodeService::PURPOSE_WITHDRAWAL, (string) $request->verification_code)) {
                return back()->withInput()->with('withdrawal_code_required', true)
                    ->withErrors(['verification_code' => 'The confirmation code is incorrect or has expired.']);
            }
        }

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
                        // Signed P/L, not the gross payout: win =
                        // payout - investment, loss = -investment (or
                        // buyout - investment for an early sell),
                        // draw = 0.00.
                        number_format(TradeResultService::settledProfit($t), 2) . '$'
                    ]);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }

        $trades = $query->latest('opened_at')->paginate(10)->withQueryString();

        return view('account.history', compact('trades', 'user', 'accountType', 'startDate', 'endDate'));
    }

    /**
     * Unified money history: every transaction type (deposit, withdrawal,
     * promo bonus, tournament entry, ...) in one list, newest first.
     * Money facts come from the transactions table only — no sample rows.
     */
    public function transactions(Request $request)
    {
        $user = $this->ensureAuth();

        $transactions = Transaction::where('user_id', $user->id)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('account.transactions', compact('user', 'transactions'));
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

    /**
     * Validate a promo code for use on a deposit.
     *
     * Returns ['promo' => ?PromoCode, 'error' => ?string, 'bonus' => float].
     * When $amount is given, the bonus for that deposit is computed and the
     * code's minimum deposit is enforced; with no amount the bonus is 0
     * (it cannot be computed yet) but the code itself is still validated.
     *
     * NOTE: the promo_codes table has no max_uses / uses_count columns, so
     * per-code usage limits cannot be enforced here. If a usage limit is
     * wanted later, add those columns via a migration first.
     */
    private function resolveDepositPromo(?string $code, ?float $amount = null): array
    {
        $code = strtoupper(trim((string) $code));

        if ($code === '') {
            return ['promo' => null, 'error' => null, 'bonus' => 0.0];
        }

        if (!Schema::hasTable('promo_codes')) {
            return ['promo' => null, 'error' => 'Promo codes are not available right now.', 'bonus' => 0.0];
        }

        $promo = PromoCode::where('code', $code)->first();

        if (!$promo) {
            return ['promo' => null, 'error' => 'Promo code "' . $code . '" is invalid.', 'bonus' => 0.0];
        }

        if (!$promo->is_active) {
            return ['promo' => null, 'error' => 'Promo code "' . $code . '" is no longer active.', 'bonus' => 0.0];
        }

        if (!$promo->is_unlimited && $promo->expires_at && $promo->expires_at->isPast()) {
            return ['promo' => null, 'error' => 'Promo code "' . $code . '" has expired.', 'bonus' => 0.0];
        }

        if (!in_array($promo->category, ['deposit_bonus', 'balance_bonus'], true)) {
            return ['promo' => null, 'error' => 'Promo code "' . $code . '" is not a deposit bonus code and cannot be used on a deposit.', 'bonus' => 0.0];
        }

        if (!in_array($promo->reward_type, ['percentage', 'fixed_amount'], true)) {
            return ['promo' => null, 'error' => 'Promo code "' . $code . '" cannot be applied to a deposit.', 'bonus' => 0.0];
        }

        if ($amount !== null && $promo->min_deposit !== null && $amount < (float) $promo->min_deposit) {
            return ['promo' => null, 'error' => 'Promo code "' . $code . '" requires a minimum deposit of $' . number_format((float) $promo->min_deposit, 2) . '.', 'bonus' => 0.0];
        }

        $bonus = 0.0;
        if ($amount !== null && $amount > 0) {
            $bonus = $promo->reward_type === 'percentage'
                ? round($amount * (float) $promo->reward_value / 100, 2)
                : round((float) $promo->reward_value, 2);
        }

        return ['promo' => $promo, 'error' => null, 'bonus' => $bonus];
    }

    /**
     * Human-readable description of what a deposit promo code gives.
     */
    private function depositPromoBonusText(PromoCode $promo): string
    {
        if ($promo->reward_type === 'percentage') {
            $value = rtrim(rtrim(number_format((float) $promo->reward_value, 2, '.', ''), '0'), '.');
            $text = 'You will get a ' . $value . '% bonus on your deposit.';
        } elseif ($promo->reward_type === 'fixed_amount') {
            $text = 'You will get a $' . number_format((float) $promo->reward_value, 2) . ' bonus on your deposit.';
        } else {
            $text = 'Bonus: ' . $promo->formatted_name . '.';
        }

        if ($promo->min_deposit !== null) {
            $text .= ' Minimum deposit $' . number_format((float) $promo->min_deposit, 2) . '.';
        }

        return $text;
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

        $resolved = $this->resolveDepositPromo($code);
        if ($resolved['error'] !== null) {
            return response()->json([
                'success' => false,
                'message' => $resolved['error'],
            ], 404);
        }

        $promo = $resolved['promo'];

        return response()->json([
            'success' => true,
            'message' => 'Promo code "' . $promo->code . '" activated! ' . $this->depositPromoBonusText($promo) . ' Continue to deposit to claim it.',
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
            'name'       => 'nullable|string|max:255',
            'country'    => 'nullable|string',
            'last_name'  => 'nullable|string|max:255',
            'birthday'   => 'nullable|date',
            'address'    => 'nullable|string|max:255',
            'nickname'   => 'nullable|string|max:255',
            'old_password' => 'nullable|string',
            'password'   => 'nullable|min:6|confirmed',
        ]);

        // Password change: the CURRENT password must be verified first.
        // A wrong old password rejects the change and nothing is saved.
        $changingPassword = $request->filled('password');
        if ($changingPassword) {
            if (!$request->filled('old_password') || !Hash::check($request->old_password, $user->password)) {
                return back()->withErrors(['old_password' => 'The current password is incorrect.']);
            }
        }

        // Only touch profile fields the submitted form actually
        // contained. The password form posts no profile fields at all,
        // so changing a password can no longer blank nickname,
        // last name, birthday or address (the old code wrote null for
        // every field the form did not send).
        $update = [];
        foreach (['name', 'country', 'last_name', 'birthday', 'address'] as $field) {
            if ($request->has($field)) {
                $update[$field] = $request->input($field);
            }
        }
        if ($request->has('nickname')) {
            $update['nickname'] = $request->nickname ? ltrim($request->nickname, '#') : null;
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/avatars'), $fileName);
            $update['avatar'] = 'uploads/avatars/' . $fileName;
        }

        if ($changingPassword) {
            $update['password'] = Hash::make($request->password);
        }

        if (!empty($update)) {
            $user->update($update);
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Save the two-step verification toggles from the Profile page.
     * (Login never enforced 2FA before; the toggles were hardcoded on.
     * They now persist the user's real choice. Enforcement at login
     * is a separate step and is NOT added here.)
     */
    public function updateTwoFactor(Request $request)
    {
        $user = $this->ensureAuth();
        $user->update([
            'login_2fa' => $request->boolean('login_2fa'),
            'withdrawal_2fa' => $request->boolean('withdrawal_2fa'),
        ]);
        return response()->json(['success' => true]);
    }

    /**
     * Send a real email-verification link. The old Resend button only
     * showed an alert and never sent anything.
     */
    public function resendVerificationEmail(Request $request)
    {
        $user = $this->ensureAuth();
        if ($user->email_verified || $user->email_verified_at) {
            return response()->json(['success' => false, 'message' => 'Your email is already verified.'], 422);
        }
        $url = URL::temporarySignedRoute('verification.verify', now()->addHours(24), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);
        try {
            Mail::html(
                '<p>Hello ' . e($user->name ?? 'trader') . ',</p>'
                . '<p>Please verify your email address for your Quotex account by clicking the link below:</p>'
                . '<p><a href="' . e($url) . '">Verify my email</a></p>'
                . '<p>This link expires in 24 hours.</p>',
                function ($message) use ($user) {
                    $message->to($user->email)->subject('Verify your email address');
                }
            );
        } catch (\Throwable $e) {
            Log::warning('Verification email failed to send', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Could not send the verification email. Please try again later.'], 500);
        }
        return response()->json(['success' => true, 'message' => 'Verification link sent to your email!']);
    }

    /** Verify an email from the signed link in the verification email. */
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::find($id);
        if (!$user || !hash_equals(sha1($user->email), (string) $hash)) {
            abort(404);
        }
        $user->email_verified = true;
        $user->email_verified_at = now();
        $user->save();
        return redirect()->route('profile')->with('success', 'Your email address has been verified.');
    }

    /**
     * Delete the logged-in user's own account.
     *
     * Same delete as AdminUserController::destroy() ($user->delete()):
     * the migrations cascade-delete the user's trades, transactions,
     * KYC verifications, referral commissions and admin logs, and
     * null out referred_by / reviewed_by references, so no other
     * user's data is touched. The two leftovers with no foreign key
     * (password_reset_tokens, sessions) are removed explicitly.
     * Unlike the admin-panel destroy, this is a self-delete, so it
     * is allowed for the account owner themselves (including an
     * admin) — the confirm dialog on the form is the safeguard
     * against an accidental click.
     */
    public function deleteAccount(Request $request)
    {
        $user = $this->ensureAuth();
        $userId = $user->id;
        $email = $user->email;

        DB::transaction(function () use ($user, $userId, $email) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            if (Schema::hasTable('sessions')) {
                DB::table('sessions')->where('user_id', $userId)->delete();
            }

            $user->delete();
        });

        Log::info('Account self-deleted', ['user_id' => $userId, 'email' => $email]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Your account has been deleted.');
    }
}
