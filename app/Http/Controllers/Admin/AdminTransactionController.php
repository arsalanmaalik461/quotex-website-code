<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Transaction;
use App\Models\User;
use App\Services\EmailTemplateService;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AdminTransactionController extends Controller
{
    public function deposits(Request $request)
    {
        $this->expireStaleOnlineDeposits();

        $status = $request->query('status', 'pending');
        if (!in_array($status, ['all', 'pending', 'approved', 'rejected'], true)) {
            $status = 'pending';
        }

        $transactions = Transaction::with('user')
            ->where('type', 'deposit')
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.transactions.deposits', compact('transactions', 'status'));
    }

    public function withdrawals(Request $request)
    {
        $status = $request->query('status', 'pending');
        if (!in_array($status, ['all', 'pending', 'approved', 'rejected'], true)) {
            $status = 'pending';
        }

        $transactions = Transaction::with('user')
            ->where('type', 'withdrawal')
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $defaultRejectionReason = EmailTemplateService::get('email.withdrawal_rejected_default_reason');
        $approvalEmailsEnabled = EmailTemplateService::approvalEmailsEnabled();

        return view('admin.transactions.withdrawals', compact(
            'transactions',
            'status',
            'defaultRejectionReason',
            'approvalEmailsEnabled'
        ));
    }

    public function all(Request $request)
    {
        $this->expireStaleOnlineDeposits();

        $transactions = Transaction::with('user')
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->type))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.transactions.all', compact('transactions'));
    }

    public function approve(Request $request, Transaction $transaction)
    {
        if ($transaction->provider === 'lgpay') {
            return back()->with('error', 'LG Pay deposits are approved only by the verified gateway callback.');
        }

        $processed = $this->approveTransaction($request, $transaction);

        if (!$processed) {
            return back()->with('error', 'Transaction already processed.');
        }

        AdminLog::record(
            'transaction_approve',
            "Approved {$processed->type} #{$processed->id} of $" . $processed->amount . " for user_id={$processed->user_id}",
            'transaction',
            $processed->id
        );

        if ($processed->type === 'withdrawal') {
            return back()->with('success', 'Withdrawal approved successfully.');
        }

        return back()->with('success', 'Transaction approved and balance updated.');
    }

    public function approveWithMail(Request $request, Transaction $transaction)
    {
        if ($transaction->type !== 'withdrawal') {
            return back()->with('error', 'Email approval is available for withdrawal requests only.');
        }

        if (!EmailTemplateService::approvalEmailsEnabled()) {
            return back()->with('error', 'Withdrawal approval emails are disabled in Email Settings.');
        }

        $processed = $this->approveTransaction($request, $transaction);

        if (!$processed) {
            return back()->with('error', 'Transaction already processed.');
        }

        AdminLog::record(
            'transaction_approve',
            "Approved {$processed->type} #{$processed->id} of $" . $processed->amount . " for user_id={$processed->user_id}",
            'transaction',
            $processed->id
        );

        try {
            EmailTemplateService::sendWithdrawalApproved($processed->fresh(['user']));
            AdminLog::record(
                'withdrawal_approval_email_sent',
                "Sent withdrawal approval email for withdrawal #{$processed->id} to user_id={$processed->user_id}.",
                'transaction',
                $processed->id
            );
        } catch (\Throwable $e) {
            Log::error('Withdrawal approval email failed.', [
                'transaction_id' => $processed->id,
                'user_id' => $processed->user_id,
                'error' => $e->getMessage(),
            ]);

            AdminLog::record(
                'withdrawal_approval_email_failed',
                "Withdrawal #{$processed->id} was approved, but approval email failed: {$e->getMessage()}",
                'transaction',
                $processed->id
            );

            return back()->with('error', 'Withdrawal approved successfully, but approval email could not be sent. Check email settings/logs.');
        }

        return back()->with('success', 'Withdrawal approved and approval email sent.');
    }

    public function reject(Request $request, Transaction $transaction)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        $reason = $request->input('reason') ?: 'Rejected by admin.';

        $processed = $this->rejectTransaction($transaction, $reason);

        if (!$processed) {
            return back()->with('error', 'Transaction already processed.');
        }

        AdminLog::record(
            'transaction_reject',
            "Rejected {$processed->type} #{$processed->id} for user_id={$processed->user_id}. Reason: {$reason}",
            'transaction',
            $processed->id
        );

        return back()->with('success', 'Transaction rejected.');
    }

    public function rejectWithMail(Request $request, Transaction $transaction)
    {
        $request->validate(['reason' => 'nullable|string|max:1000']);

        if ($transaction->type !== 'withdrawal') {
            return back()->with('error', 'Email rejection is available for withdrawal requests only.');
        }

        $defaultReason = trim(EmailTemplateService::get('email.withdrawal_rejected_default_reason'));
        $reason = trim((string) $request->input('reason'));

        if ($reason === '') {
            $reason = $defaultReason;
        }

        if ($reason === '') {
            return back()->with('error', 'Please configure a default withdrawal rejection reason or enter a custom reason before sending.');
        }

        $processed = $this->rejectTransaction($transaction, $reason);

        if (!$processed) {
            return back()->with('error', 'Transaction already processed.');
        }

        AdminLog::record(
            'withdrawal_reject_with_mail',
            "Rejected withdrawal #{$processed->id} and queued rejection email for user_id={$processed->user_id}. Reason: {$reason}",
            'transaction',
            $processed->id
        );

        try {
            EmailTemplateService::sendWithdrawalRejected($processed->fresh(['user']), $reason);
        } catch (\Throwable $e) {
            Log::error('Withdrawal rejection email failed.', [
                'transaction_id' => $processed->id,
                'user_id' => $processed->user_id,
                'error' => $e->getMessage(),
            ]);

            AdminLog::record(
                'withdrawal_rejection_email_failed',
                "Withdrawal #{$processed->id} was rejected, but email failed: {$e->getMessage()}",
                'transaction',
                $processed->id
            );

            return back()->with('error', 'Withdrawal rejected and balance refunded, but email could not be sent. Check email settings/logs.');
        }

        return back()->with('success', 'Withdrawal rejected, balance refunded, and email sent.');
    }

    private function rejectTransaction(Transaction $transaction, string $reason): ?Transaction
    {
        return DB::transaction(function () use ($transaction, $reason) {
            $lockedTransaction = Transaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            if ($lockedTransaction->status !== 'pending') {
                return null;
            }

            if ($lockedTransaction->type === 'withdrawal') {
                $user = User::whereKey($lockedTransaction->user_id)->lockForUpdate()->firstOrFail();
                $user->live_balance = Money::add($user->live_balance, $lockedTransaction->amount);
                $user->save();
            }

            // P0-3 (2026-11-18): reverse a promo bonus that was already
            // credited for this deposit. That only happens for deposits
            // made before this fix, when the bonus was credited
            // immediately while the deposit was still pending. The bonus
            // row is linked by its notes ("for deposit #<id>"). The
            // balance is floored at zero: if the user already spent or
            // withdrew part of the bonus, the reversal takes only what
            // is left rather than driving the balance negative.
            if ($lockedTransaction->type === 'deposit') {
                $linkedBonuses = Transaction::where('user_id', $lockedTransaction->user_id)
                    ->where('type', 'bonus')
                    ->where('status', 'approved')
                    ->where('notes', 'like', '%for deposit #' . $lockedTransaction->id . '%')
                    ->lockForUpdate()
                    ->get();
                if ($linkedBonuses->isNotEmpty()) {
                    $user = User::whereKey($lockedTransaction->user_id)->lockForUpdate()->firstOrFail();
                    foreach ($linkedBonuses as $bonus) {
                        $user->live_balance = Money::maxZero(
                            Money::subtract($user->live_balance, $bonus->amount)
                        );
                        $bonus->update([
                            'status' => 'rejected',
                            'notes' => $bonus->notes . ' Reversed: deposit #' . $lockedTransaction->id . ' was rejected.',
                        ]);
                    }
                    $user->save();
                }
            }

            $lockedTransaction->update([
                'status' => 'rejected',
                'notes'  => $reason,
                'processed_at' => now(),
            ]);

            return $lockedTransaction;
        });
    }

    private function approveTransaction(Request $request, Transaction $transaction): ?Transaction
    {
        if ($transaction->provider === 'lgpay') {
            return null;
        }

        return DB::transaction(function () use ($request, $transaction) {
            $lockedTransaction = Transaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            if ($lockedTransaction->status !== 'pending') {
                return null;
            }

            if ($lockedTransaction->provider === 'lgpay') {
                return null;
            }

            if ($lockedTransaction->type === 'deposit') {
                $user = User::whereKey($lockedTransaction->user_id)->lockForUpdate()->firstOrFail();
                $user->live_balance = Money::add($user->live_balance, $lockedTransaction->amount);
                $user->save();

                // P0-3 (2026-11-18): credit the promo bonus here, only
                // now that the deposit itself is approved. The promo was
                // stored on the deposit row at submission time (migration
                // 2026_11_18_000014) and nothing was credited while it
                // was pending, so a rejected deposit never mints a bonus
                // and a bonus can never be withdrawn before approval.
                // Idempotent: if a bonus row for this deposit already
                // exists (e.g. credited under the old immediate-credit
                // behaviour, or a double approve attempt), it is not
                // credited again.
                $promoBonus = Schema::hasColumn('transactions', 'promo_bonus_amount')
                    ? (float) ($lockedTransaction->promo_bonus_amount ?? 0)
                    : 0.0;
                $promoCode = Schema::hasColumn('transactions', 'promo_code')
                    ? (string) ($lockedTransaction->promo_code ?? '')
                    : '';
                if ($promoBonus > 0) {
                    $alreadyCredited = Transaction::where('user_id', $lockedTransaction->user_id)
                        ->where('type', 'bonus')
                        ->where('status', 'approved')
                        ->where('notes', 'like', '%for deposit #' . $lockedTransaction->id . '%')
                        ->exists();
                    if (!$alreadyCredited) {
                        $user->live_balance = Money::add($user->live_balance, $promoBonus);
                        $user->save();

                        Transaction::create([
                            'user_id' => $lockedTransaction->user_id,
                            'account_type' => 'live',
                            'type' => 'bonus',
                            'amount' => $promoBonus,
                            'method' => $promoCode !== '' ? 'Promo code ' . $promoCode : 'Promo bonus',
                            'status' => 'approved',
                            'processed_at' => now(),
                            'notes' => 'Promo bonus ' . $promoCode . ' for deposit #' . $lockedTransaction->id . ' of $' . number_format((float) $lockedTransaction->amount, 2) . '.',
                        ]);
                    }
                }
            }

            $lockedTransaction->update([
                'status' => 'approved',
                'notes' => $request->input('note', $lockedTransaction->notes),
                'processed_at' => now(),
            ]);

            if ($lockedTransaction->type === 'deposit') {
                \App\Services\ReferralService::processDepositCommission($lockedTransaction->fresh());
            }

            return $lockedTransaction;
        });
    }

    private function expireStaleOnlineDeposits(): void
    {
        Transaction::where('type', 'deposit')
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
    }
}
