<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\AppSetting;
use App\Models\PaymentCategory;
use App\Models\Trade;
use App\Models\PaymentMethod;
use App\Models\Tournament;
use App\Models\PromoCode;
use App\Models\MarketPurchase;
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
            // The real floor is per-method (see the check below):
            // $10 for most methods, $50 for major crypto methods.
            // A flat min:5 here would let a $30 crypto deposit past
            // validation before the method is even resolved.
            'amount'            => 'required|numeric|min:1',
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

        // The submitted method name must be the resolved method's
        // name — a free-text method that matches no PaymentMethod row
        // is rejected instead of being stored.
        if (strtolower(trim((string) $request->input('method'))) !== strtolower(trim((string) $paymentMethod->name))) {
            return back()->withInput()->withErrors(['method' => 'Selected payment method is unavailable. Please choose a payment method from the list.']);
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

        // Per-method minimum, enforced server-side: the admin-set
        // min_amount column where set, floored at $10 ($50 for major
        // crypto methods) — PaymentMethod::effectiveMinAmount().
        // The message names the method and its minimum so the user
        // knows exactly why a deposit was rejected.
        $minAmount = $paymentMethod->effectiveMinAmount();
        if ($amount < $minAmount) {
            return back()->withInput()->withErrors(['amount' => 'The minimum deposit for ' . $paymentMethod->name . ' is $' . number_format($minAmount, 2) . '. Please enter at least $' . number_format($minAmount, 2) . '.']);
        }
        if ($amount > (float) $paymentMethod->max_amount) {
            return back()->withInput()->withErrors(['amount' => 'The maximum deposit for ' . $paymentMethod->name . ' is $' . number_format((float) $paymentMethod->max_amount, 2) . '.']);
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

        // P0-3 (2026-11-18): the promo bonus is NOT credited here. It
        // used to be credited immediately while the deposit was still
        // pending, so a user could deposit with a promo code, withdraw
        // the bonus at once, and keep it even if the admin later
        // rejected the deposit for a fake proof. The promo code and
        // bonus amount are now stored on the deposit row (migration
        // 2026_11_18_000014) and the bonus is credited only when the
        // admin approves the deposit — see
        // AdminTransactionController::approveTransaction(). A deposit
        // rejected before approval never mints a bonus.
        $depositData = [
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
        ];
        if ($appliedPromo !== null && $promoBonus > 0) {
            $depositData['notes'] .= ' Promo bonus ' . $appliedPromo->code . ' ($' . number_format($promoBonus, 2) . ') pending — credited only when this deposit is approved.';
            if (Schema::hasColumn('transactions', 'promo_code')) {
                $depositData['promo_code'] = $appliedPromo->code;
            }
            if (Schema::hasColumn('transactions', 'promo_bonus_amount')) {
                $depositData['promo_bonus_amount'] = $promoBonus;
            }
        }
        $deposit = Transaction::create($depositData);

        $successMessage = 'Deposit request of $' . number_format($amount, 2) . ' submitted successfully! It is pending admin review and will be added to your balance once verified.';
        if ($appliedPromo !== null && $promoBonus > 0) {
            $successMessage .= ' Promo bonus of $' . number_format($promoBonus, 2) . ' from code ' . $appliedPromo->code . ' will be credited to your live balance once this deposit is approved.';
        }

        return redirect()->route('deposit')->with('success', $successMessage);
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

        // Same-day withdrawal count, for the 3% fee rule: the first 2
        // withdrawals created today are fee-free; the 3rd and every
        // later one today is charged 3%. The view uses this to show
        // the fee preview before the user submits.
        $withdrawalsToday = self::countWithdrawalsToday($user->id);

        return view('account.withdrawal', compact('user', 'methods', 'recentTransactions', 'isVerified', 'emailVerified', 'withdrawalsToday'));
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
            // The real floor is per-method (see the check below):
            // $10 for most methods, $50 for major crypto methods.
            'amount'            => 'required|numeric|min:1',
            'method'            => 'required|string',
            'payment_method_id' => 'nullable|integer',
            'purse'             => 'nullable|string|max:255',
            'destination'       => 'nullable|string|max:255',
            'first_name'        => 'nullable|string|max:100',
            'last_name'         => 'nullable|string|max:100',
            'receive_type'      => 'nullable|string|max:100',
            'verification_code' => 'nullable|string|max:10',
        ]);

        // Method must be a real PaymentMethod row — no free-text
        // method is accepted. Resolved by id when the form sends one,
        // otherwise by exact (case-insensitive) name, among active
        // withdrawal methods only.
        $paymentMethod = $this->resolveWithdrawalMethod($request);
        if (!$paymentMethod) {
            return back()->withInput()->withErrors(['method' => 'Selected payment method is unavailable. Please choose a payment method from the list.']);
        }

        // Per-method minimum, enforced server-side: the admin-set
        // min_amount column where set, floored at $10 ($50 for major
        // crypto methods) — PaymentMethod::effectiveMinAmount().
        $minAmount = $paymentMethod->effectiveMinAmount();
        if ((float) $validated['amount'] < $minAmount) {
            return back()->withInput()->withErrors(['amount' => 'The minimum withdrawal for ' . $paymentMethod->name . ' is $' . number_format($minAmount, 2) . '. Please enter at least $' . number_format($minAmount, 2) . '.']);
        }
        if ((float) $validated['amount'] > (float) $paymentMethod->max_amount) {
            return back()->withInput()->withErrors(['amount' => 'The maximum withdrawal for ' . $paymentMethod->name . ' is $' . number_format((float) $paymentMethod->max_amount, 2) . '.']);
        }

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

        $amount = (float) $validated['amount'];
        $notes = 'Withdrawal via ' . $paymentMethod->name;
        if ($request->filled('first_name') || $request->filled('last_name')) {
            $notes .= ' | Name: ' . trim($request->first_name . ' ' . $request->last_name);
        }
        if ($request->filled('receive_type')) {
            $notes .= ' | Type: ' . $request->receive_type;
        }

        // 3% fee from the 3rd same-day withdrawal (server-side, so it
        // cannot be bypassed from the client): the count is taken
        // inside the transaction, under the user lock, so two requests
        // racing each other cannot both slip in as "2nd withdrawal".
        // The full requested amount is debited from the live balance;
        // the fee is deducted from the amount actually sent.
        $chargedFee = 0.0;
        $created = DB::transaction(function () use ($amount, $destination, $notes, $paymentMethod, &$chargedFee) {
            $user = User::whereKey(Auth::id())->lockForUpdate()->firstOrFail();

            if (Money::greaterThan($amount, $user->live_balance ?? 0)) {
                return null;
            }

            $priorToday = self::countWithdrawalsToday($user->id);
            $withdrawalNumber = $priorToday + 1;
            $fee = self::withdrawalFee($amount, $priorToday);
            $chargedFee = $fee;
            $net = round($amount - $fee, 2);

            $user->live_balance = Money::subtract($user->live_balance, $amount);
            $user->save();

            $txNotes = $notes;
            if ($fee > 0) {
                $txNotes .= ' | 3% fee: $' . number_format($fee, 2) . ' (withdrawal #' . $withdrawalNumber . ' today; first 2 same-day withdrawals are fee-free). Amount to send: $' . number_format($net, 2) . '.';
            }

            $data = [
                'user_id'        => $user->id,
                'account_type'   => 'live',
                'type'           => 'withdrawal',
                'amount'         => $amount,
                'method'         => $paymentMethod->name,
                'payment_method_id' => $paymentMethod->id,
                'wallet_address' => $destination,
                'status'         => 'pending',
                'notes'          => $txNotes,
            ];
            // Guarded so the request still works on a server where
            // the fee migration has not run yet (fee then lives in
            // the notes only, and is 0 in the columns once migrated).
            if (Schema::hasColumn('transactions', 'fee_amount')) {
                $data['fee_amount'] = $fee;
            }
            if (Schema::hasColumn('transactions', 'net_amount')) {
                $data['net_amount'] = $net;
            }

            return Transaction::create($data);
        });

        if (!$created) {
            $user = Auth::user()->fresh();
            return back()->withErrors(['amount' => 'Insufficient live balance. You currently have $' . number_format((float) ($user->live_balance ?? 0), 2) . ' available.']);
        }

        $fee = $chargedFee;
        $net = $fee > 0 ? round($amount - $fee, 2) : $amount;
        $success = 'Withdrawal request of $' . number_format($amount, 2) . ' submitted! It will be sent after admin approval.';
        if ($fee > 0) {
            $success .= ' A 3% fee of $' . number_format($fee, 2) . ' applies (3rd or later withdrawal today), so $' . number_format($net, 2) . ' will be sent to you.';
        }

        return redirect()->route('withdrawal')->with('success', $success);
    }

    /**
     * Withdrawal fee rule (real Quotex / withdrawal-page FAQ): the
     * first 2 withdrawals created on the same day are fee-free; the
     * 3rd and every later same-day withdrawal is charged 3% of the
     * requested amount, deducted from the amount sent.
     */
    public const WITHDRAWAL_FEE_RATE = 0.03;
    public const FREE_WITHDRAWALS_PER_DAY = 2;

    /** Withdrawals this user has created today (any status). */
    public static function countWithdrawalsToday(int $userId): int
    {
        return Transaction::where('user_id', $userId)
            ->where('type', 'withdrawal')
            ->whereDate('created_at', today())
            ->count();
    }

    /** Fee for a withdrawal, given how many the user already made today. */
    public static function withdrawalFee(float $amount, int $priorWithdrawalsToday): float
    {
        if ($priorWithdrawalsToday < self::FREE_WITHDRAWALS_PER_DAY) {
            return 0.0;
        }

        return round($amount * self::WITHDRAWAL_FEE_RATE, 2);
    }

    /**
     * Resolve the withdrawal's payment method against real
     * PaymentMethod rows (active, withdrawal/both). By id when the
     * form sends payment_method_id, otherwise by exact name
     * (case-insensitive) — the form's select currently posts names.
     * Returns null when no real row matches (free-text rejected).
     */
    private function resolveWithdrawalMethod(Request $request): ?PaymentMethod
    {
        if (!Schema::hasTable('payment_methods')) {
            return null;
        }

        $query = PaymentMethod::active()->withdrawals();

        if ($request->filled('payment_method_id')) {
            return (clone $query)->where('id', $request->integer('payment_method_id'))->first();
        }

        $name = strtolower(trim((string) $request->input('method', '')));
        if ($name === '') {
            return null;
        }

        return $query->get()->first(
            fn (PaymentMethod $method) => strtolower(trim($method->name)) === $name
        );
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
                        ($t->asset->payout_rate ?? 90) . '%',
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

        // Filters (all optional, GET so they survive pagination via
        // withQueryString): type, status, date range, free-text
        // search over method / notes / provider reference.
        $type = $request->query('type', '');
        $status = $request->query('status', '');
        $from = $request->query('from', '');
        $to = $request->query('to', '');
        $search = trim((string) $request->query('q', ''));

        $allowedTypes = ['deposit', 'withdrawal', 'bonus', 'tournament_entry'];
        $allowedStatuses = ['pending', 'processing', 'approved', 'rejected', 'failed'];

        $query = Transaction::where('user_id', $user->id);

        if (in_array($type, $allowedTypes, true)) {
            $query->where('type', $type);
        }
        if (in_array(strtolower($status), $allowedStatuses, true)) {
            $query->where('status', strtolower($status));
        }
        if ($from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $query->whereDate('created_at', '<=', $to);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('method', 'like', '%' . $search . '%')
                    ->orWhere('notes', 'like', '%' . $search . '%')
                    ->orWhere('provider_reference', 'like', '%' . $search . '%')
                    ->orWhere('transaction_id', 'like', '%' . $search . '%');
            });
        }

        $transactions = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $filters = ['type' => $type, 'status' => $status, 'from' => $from, 'to' => $to, 'q' => $search];

        return view('account.transactions', compact('user', 'transactions', 'filters'));
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

        $this->seedMarketPromoCodes();

        $activePromos = collect([]);
        if (Schema::hasTable('promo_codes')) {
            $activePromos = PromoCode::active()->get()->groupBy('category');
        }

        // Promo codes this user already bought (Buy button shows "Owned").
        $ownedPromoIds = [];
        if (Schema::hasTable('market_purchases')) {
            $ownedPromoIds = MarketPurchase::where('user_id', $user->id)
                ->pluck('promo_code_id')->all();
        }

        return view('account.market', compact('user', 'activePromos', 'ownedPromoIds'));
    }

    /**
     * Make sure every Market category has items.
     *
     * The old seed ran only when the table was completely empty and only
     * created three deposit_bonus codes, so on a fresh install 5 of the 6
     * cards were empty — and on an existing install (where the three
     * deposit codes already exist) the other categories never appeared at
     * all. This is idempotent: firstOrCreate by code, so existing rows
     * (including admin edits) are never overwritten, and the price is
     * backfilled only where it was never set (NULL, i.e. rows created
     * before the price column existed).
     */
    private function seedMarketPromoCodes(): void
    {
        if (!Schema::hasTable('promo_codes')) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {
            }
        }

        if (!Schema::hasTable('promo_codes')) {
            return;
        }

        $hasPriceColumn = Schema::hasColumn('promo_codes', 'price');

        foreach (PromoCode::seedDefinitions() as $definition) {
            $code = $definition['code'];
            unset($definition['code']);

            PromoCode::firstOrCreate(['code' => $code], $definition);

            if ($hasPriceColumn) {
                PromoCode::where('code', $code)->whereNull('price')
                    ->update(['price' => $definition['price'] ?? 0]);
            }
        }
    }

    /**
     * Buy a Market item (a promo code) with the LIVE balance.
     *
     * POST /market/purchase { promo_code_id, idempotency_key }.
     *
     * Flow: validate the code is still active, check funds, then inside
     * one DB transaction with the user row locked: re-check the
     * idempotency key (a rapid double-click replays the same key and the
     * second submit returns the first purchase instead of charging
     * again), reject a second purchase of the same code by the same user
     * (409), re-check funds under the lock, deduct the price from the
     * LIVE balance, record a `market_purchase` transaction, grant what
     * the item promises (see grantMarketPurchase()), and store the
     * purchase row. The price is read from the ITEM, never from the
     * request — a client cannot set its own price.
     *
     * This is a purchase guard only: trade Buy/Sell stays unguarded per
     * the user's rule (rapid repeat trades are intentional).
     */
    public function purchasePromo(Request $request)
    {
        $user = $this->ensureAuth();

        if (!Schema::hasTable('market_purchases')) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {
            }
        }

        if (!Schema::hasTable('market_purchases')) {
            return response()->json([
                'success' => false,
                'message' => 'Market purchases are not available right now. Please try again shortly.',
            ], 503);
        }

        $validated = $request->validate([
            'promo_code_id'   => 'required|integer',
            'idempotency_key' => 'required|string|max:64',
        ]);

        $promo = PromoCode::find($validated['promo_code_id']);
        if (!$promo) {
            return response()->json([
                'success' => false,
                'message' => 'This Market item was not found.',
            ], 404);
        }

        if (!$promo->is_active || (!$promo->is_unlimited && $promo->expires_at && $promo->expires_at->isPast())) {
            return response()->json([
                'success' => false,
                'message' => 'Promo code "' . $promo->code . '" is no longer available.',
            ], 422);
        }

        $price = $promo->priceAmount();
        $key = $validated['idempotency_key'];

        // Fast path: this exact submit was already processed (double-click).
        $existing = MarketPurchase::where('user_id', $user->id)
            ->where('idempotency_key', $key)->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'already_purchased' => true,
                'message' => 'This purchase was already completed.',
                'granted' => $existing->granted_summary,
                'new_balance' => (float) ($user->live_balance ?? 0),
                'purchase_id' => $existing->id,
            ]);
        }

        if (Money::greaterThan($price, $user->live_balance ?? 0)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient live balance. This item costs $' . number_format($price, 2)
                    . ' but you have $' . number_format((float) ($user->live_balance ?? 0), 2) . ' available on your live account.',
            ], 422);
        }

        try {
            $result = DB::transaction(function () use ($user, $promo, $price, $key) {
                // Lock the user row so two parallel purchases cannot double-charge.
                $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

                // Idempotency re-check under the lock (parallel double-click).
                $byKey = MarketPurchase::where('user_id', $lockedUser->id)
                    ->where('idempotency_key', $key)->first();
                if ($byKey) {
                    return ['purchase' => $byKey, 'already' => true, 'balance' => (float) $lockedUser->live_balance];
                }

                // One purchase per user per code.
                $owned = MarketPurchase::where('user_id', $lockedUser->id)
                    ->where('promo_code_id', $promo->id)->first();
                if ($owned) {
                    return ['error' => 'already_owned', 'purchase' => $owned];
                }

                // Funds re-check under the lock (balance may have changed).
                if (Money::greaterThan($price, $lockedUser->live_balance ?? 0)) {
                    return ['error' => 'insufficient'];
                }

                if ($price > 0) {
                    $lockedUser->live_balance = Money::subtract($lockedUser->live_balance, $price);
                    $lockedUser->save();

                    Transaction::create([
                        'user_id'      => $lockedUser->id,
                        'account_type' => 'live',
                        'type'         => 'market_purchase',
                        'amount'       => $price,
                        'method'       => 'market',
                        'status'       => 'approved',
                        'processed_at' => now(),
                        'notes'        => 'Market purchase: ' . $promo->code . ' (' . $promo->formatted_name . ').',
                    ]);
                }

                $granted = $this->grantMarketPurchase($lockedUser, $promo);

                $purchase = MarketPurchase::create([
                    'user_id'         => $lockedUser->id,
                    'promo_code_id'   => $promo->id,
                    'price_paid'      => $price,
                    'idempotency_key' => $key,
                    'status'          => 'completed',
                    'granted_summary' => $granted,
                ]);

                return [
                    'purchase' => $purchase,
                    'already'  => false,
                    'balance'  => (float) $lockedUser->live_balance,
                    'granted'  => $granted,
                ];
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Unique-index race (same key, or same user+code, slipped in
            // between the checks): the other submit won, so return its
            // purchase instead of charging again.
            if ((string) $e->getCode() === '23000') {
                $winner = MarketPurchase::where('user_id', $user->id)
                    ->where('idempotency_key', $key)->first()
                    ?? MarketPurchase::where('user_id', $user->id)
                        ->where('promo_code_id', $promo->id)->first();
                if ($winner) {
                    return response()->json([
                        'success' => true,
                        'already_purchased' => true,
                        'message' => 'This purchase was already completed.',
                        'granted' => $winner->granted_summary,
                        'new_balance' => (float) ($user->live_balance ?? 0),
                        'purchase_id' => $winner->id,
                    ]);
                }
            }
            throw $e;
        }

        if (($result['error'] ?? null) === 'already_owned') {
            return response()->json([
                'success' => false,
                'message' => 'You already own promo code "' . $promo->code . '". Each code can be bought once.',
            ], 409);
        }

        if (($result['error'] ?? null) === 'insufficient') {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient live balance. This item costs $' . number_format($price, 2) . '.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'already_purchased' => (bool) ($result['already'] ?? false),
            'message' => ($result['already'] ?? false)
                ? 'This purchase was already completed.'
                : 'Promo code "' . $promo->code . '" purchased successfully!',
            'granted' => $result['granted'] ?? null,
            'new_balance' => $result['balance'],
            'purchase_id' => $result['purchase']->id,
        ]);
    }

    /**
     * Grant what a purchased promo code promises, from the item's data.
     *
     * Only balance_bonus / fixed_amount can be granted here: its tooltip
     * promises instant bonus funds, so the reward is credited to the
     * live balance and recorded as a `bonus` transaction. A deposit
     * bonus is credited by the existing deposit flow when the user
     * deposits with the code. Risk-free, cashback, turnover and
     * cancel-points effects have no engine behind them (no settlement
     * logic exists for them), so nothing is granted — see
     * PromoCode::grantDescription(), which is what the modal shows
     * before the user pays. Runs inside the purchase transaction with
     * the user row already locked.
     */
    private function grantMarketPurchase(User $lockedUser, PromoCode $promo): string
    {
        if ($promo->category === 'balance_bonus' && $promo->reward_type === 'fixed_amount') {
            $bonus = (float) $promo->reward_value;
            if ($bonus > 0) {
                $lockedUser->live_balance = Money::add($lockedUser->live_balance, $bonus);
                $lockedUser->save();

                Transaction::create([
                    'user_id'      => $lockedUser->id,
                    'account_type' => 'live',
                    'type'         => 'bonus',
                    'amount'       => $bonus,
                    'method'       => 'market',
                    'status'       => 'approved',
                    'processed_at' => now(),
                    'notes'        => 'Balance bonus from Market purchase: ' . $promo->code . ' ($' . number_format($bonus, 2) . ').',
                ]);
            }
        }

        return $promo->grantDescription();
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

        // P0-4 (2026-11-18): no fallback. The old code returned
        // Tournament::first() — or a synthetic "Crazy Wednesday" with
        // id 933 that exists in no database — when the requested id
        // was not found. The detail view then rendered a live Join
        // button for that id, and the register route (implicit
        // route-model binding) 404s for id 933; worse, when the table
        // was non-empty the fallback registered (and charged) the user
        // for a different tournament than the card they clicked.
        $tournament = Tournament::find($id);
        if (!$tournament) {
            abort(404);
        }

        return view('account.tournament_detail', compact('user', 'tournament'));
    }

    public function analytics(Request $request)
    {
        $user = $this->ensureAuth();

        $trades = Trade::where('user_id', $user->id)->get();
        $tradesCount = $trades->count();

        // Profit stats MUST come from TradeResultService::settledProfit().
        // The old code summed 'profit' and 'amount' — attributes that do
        // not exist on the Trade model (its columns are investment /
        // payout) — so every profit figure here was 0. settledProfit()
        // is the single correct source: win = payout - investment,
        // loss = credited amount (0, or the early-sell buyout) -
        // investment, draw = 0. Pending trades are excluded: a pending
        // trade has no result yet, and settledProfit() on one would
        // read as -investment.
        $settled = $trades->where('result', '!=', 'pending');
        $profitById = $settled->mapWithKeys(
            fn (Trade $trade) => [$trade->id => TradeResultService::settledProfit($trade)]
        );
        $profitOf = fn (Trade $trade) => (float) ($profitById[$trade->id] ?? 0);

        $winningTrades = $settled->where('result', 'win');
        $profitableCount = $winningTrades->count();
        $tradesProfit = round($profitById->sum(), 2);
        $netTurnover = round($settled->sum('investment'), 2);
        $avgProfit = $profitableCount > 0 ? round($winningTrades->sum($profitOf) / $profitableCount, 2) : 0;
        $minTrade = $tradesCount > 0 ? (float) $trades->min('investment') : 0;
        $maxTrade = $tradesCount > 0 ? (float) $trades->max('investment') : 0;
        $maxProfit = $profitableCount > 0 ? round($winningTrades->max($profitOf), 2) : 0;

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
