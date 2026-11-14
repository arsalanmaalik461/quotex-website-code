<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Services\LGPayClient;
use App\Services\LGPaySignature;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LGPayController extends Controller
{
    public function initiate(Request $request, LGPayClient $client)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'payment_method_id' => 'required|integer',
            'amount' => ['required', 'numeric', 'gt:0', 'regex:/^\d+(\.\d{1,2})?$/'],
        ]);

        if (AppSetting::get('lgpay.enabled', '0') !== '1') {
            return back()->withErrors(['amount' => 'Online payment is currently unavailable.']);
        }

        $method = PaymentMethod::where('provider', 'lgpay')
            ->where('is_active', true)
            ->where('id', $request->integer('payment_method_id'))
            ->firstOrFail();

        $amount = round((float) $request->input('amount'), 2);
        $minAmount = $method->effectiveMinAmount();
        if ($amount < $minAmount) {
            return back()->withErrors(['amount' => 'The minimum deposit for ' . $method->name . ' is $' . number_format($minAmount, 2) . '. Please enter at least $' . number_format($minAmount, 2) . '.']);
        }
        if ($amount > (float) $method->max_amount) {
            return back()->withErrors(['amount' => 'The maximum deposit for ' . $method->name . ' is $' . number_format((float) $method->max_amount, 2) . '.']);
        }

        $conversionRate = round((float) $method->conversion_rate, 4);
        if ($conversionRate <= 0) {
            Log::error('LG Pay conversion rate invalid', ['payment_method_id' => $method->id]);
            return back()->withErrors(['amount' => 'Online payment conversion rate is not configured.']);
        }

        $gatewayAmount = round($amount * $conversionRate, 2);
        $gatewayMoney = (int) round($gatewayAmount * 100);

        if (!config('services.lgpay.app_id') || !config('services.lgpay.secret_key')) {
            Log::error('LG Pay credentials missing');
            return back()->withErrors(['amount' => 'Payment could not be initiated. Please try again.']);
        }

        $orderId = $this->makeOrderId();
        $transactionData = [
            'user_id' => Auth::id(),
            'account_type' => 'live',
            'type' => 'deposit',
            'amount' => $amount,
            'converted_amount' => $gatewayAmount,
            'currency' => 'USD',
            'method' => $method->name,
            'payment_method_id' => $method->id,
            'status' => 'processing',
            'notes' => 'Online auto payment started. Waiting for gateway confirmation.',
            'provider' => 'lgpay',
            'provider_method' => $method->provider_code,
            'merchant_order_id' => $orderId,
            'provider_status' => 'created',
        ];

        if (Schema::hasColumn('transactions', 'gateway_amount')) {
            $transactionData['gateway_amount'] = $gatewayAmount;
        }
        if (Schema::hasColumn('transactions', 'gateway_currency')) {
            $transactionData['gateway_currency'] = 'PKR';
        }
        if (Schema::hasColumn('transactions', 'gateway_conversion_rate')) {
            $transactionData['gateway_conversion_rate'] = $conversionRate;
        }

        $transaction = Transaction::create($transactionData);

        $payload = [
            'app_id' => config('services.lgpay.app_id'),
            'trade_type' => $method->trade_type,
            'order_sn' => $orderId,
            'money' => $gatewayMoney,
            'notify_url' => route('lgpay.callback'),
            'ip' => $request->ip(),
            'remark' => 'tx=' . $transaction->id . ';method=' . $method->provider_code,
        ];

        Log::info('LG Pay create order', [
            'transaction_id' => $transaction->id,
            'order_sn' => $orderId,
            'method' => $method->provider_code,
            'trade_type' => $method->trade_type,
            'site_amount_usd' => $amount,
            'gateway_amount_pkr' => $gatewayAmount,
            'conversion_rate' => $conversionRate,
        ]);

        $response = $client->createOrder($payload);
        $transaction->update(['provider_payload' => $this->safePayload($response)]);

        if ((int) ($response['status'] ?? 0) === 1 && !empty($response['data']['pay_url'])) {
            return redirect()->away($response['data']['pay_url']);
        }

        Log::warning('LG Pay create failed', [
            'transaction_id' => $transaction->id,
            'order_sn' => $orderId,
            'gateway_status' => $response['status'] ?? null,
            'gateway_msg' => $response['msg'] ?? null,
        ]);

        $transaction->update([
            'status' => 'rejected',
            'provider_status' => 'create_failed',
            'processed_at' => now(),
            'notes' => 'Online auto payment could not be started.',
        ]);

        return redirect()->route('lgpay.return', $transaction)->with('error', 'Payment could not be initiated. Please try again.');
    }

    public function callback(Request $request)
    {
        $remoteIp = $request->ip();
        $allowedIps = config('services.lgpay.callback_ips', []);
        if (!empty($allowedIps) && !in_array($remoteIp, $allowedIps, true)) {
            Log::warning('LG Pay callback rejected IP', ['ip' => $remoteIp]);
            return response('forbidden', 403);
        }

        $payload = $request->all();
        if (!LGPaySignature::verify($payload, (string) config('services.lgpay.secret_key'))) {
            Log::warning('LG Pay callback bad signature', ['ip' => $remoteIp, 'order_sn' => $payload['order_sn'] ?? null]);
            return response('bad sign', 400);
        }

        $orderId = (string) ($payload['order_sn'] ?? '');
        $status = (string) ($payload['status'] ?? '');
        $paid = in_array(strtolower($status), ['1', 'success', 'paid', 'completed', 'approved'], true);

        try {
            DB::transaction(function () use ($payload, $orderId, $status, $paid) {
                $transaction = Transaction::where('merchant_order_id', $orderId)->lockForUpdate()->first();
                if (!$transaction || $transaction->provider !== 'lgpay') {
                    Log::warning('LG Pay callback unknown order', ['order_sn' => $orderId]);
                    return;
                }

                $expectedAmount = (float) ($transaction->gateway_amount ?: $transaction->converted_amount ?: $transaction->amount);
                $expectedMoney = (int) round($expectedAmount * 100);
                $callbackMoney = (int) ($payload['money'] ?? 0);
                if ($callbackMoney !== $expectedMoney) {
                    $transaction->update([
                        'status' => 'rejected',
                        'provider_status' => 'amount_mismatch',
                        'provider_payload' => $this->safePayload($payload),
                        'processed_at' => now(),
                        'notes' => 'Online auto payment amount mismatch.',
                    ]);
                    Log::warning('LG Pay amount mismatch', ['transaction_id' => $transaction->id, 'expected' => $expectedMoney, 'received' => $callbackMoney]);
                    return;
                }

                if (!in_array($transaction->status, ['pending', 'processing'], true)) {
                    Log::info('LG Pay duplicate callback ignored', ['transaction_id' => $transaction->id, 'status' => $transaction->status]);
                    return;
                }

                if (!$paid) {
                    $transaction->update([
                        'status' => 'rejected',
                        'provider_status' => $status,
                        'provider_reference' => $payload['platform_order_sn'] ?? $payload['trade_no'] ?? null,
                        'provider_payload' => $this->safePayload($payload),
                        'processed_at' => now(),
                        'notes' => 'Online auto payment failed or was cancelled.',
                    ]);
                    return;
                }

                $user = $transaction->user()->lockForUpdate()->first();
                $user->live_balance = Money::add($user->live_balance, $transaction->amount);
                $user->save();

                $transaction->update([
                    'status' => 'approved',
                    'provider_status' => $status,
                    'provider_reference' => $payload['platform_order_sn'] ?? $payload['trade_no'] ?? null,
                    'provider_payload' => $this->safePayload($payload),
                    'processed_at' => now(),
                    'notes' => 'Online auto payment confirmed.',
                ]);

                \App\Services\ReferralService::processDepositCommission($transaction);
            });
        } catch (\Throwable $e) {
            Log::error('LG Pay callback processing failed', ['order_sn' => $orderId, 'error' => $e->getMessage()]);
            return response('error', 500);
        }

        return response('ok');
    }

    public function paymentReturn(Transaction $transaction)
    {
        abort_unless(Auth::check() && $transaction->user_id === Auth::id(), 403);

        return redirect()->route('deposit')->with(
            $transaction->status === 'approved' ? 'success' : 'error',
            $transaction->status === 'approved'
                ? 'Payment successful. Your balance has been updated.'
                : ($transaction->status === 'rejected' ? 'Payment failed. No balance was added.' : 'Your payment is being processed.')
        );
    }

    private function makeOrderId(): string
    {
        do {
            $orderId = 'PKR' . now()->format('YmdHis') . Str::upper(Str::random(6));
        } while (Transaction::where('merchant_order_id', $orderId)->exists());

        return $orderId;
    }

    private function safePayload(array $payload): array
    {
        unset($payload['sign'], $payload['secret_key'], $payload['key']);
        return $payload;
    }
}
