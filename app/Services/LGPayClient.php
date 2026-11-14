<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LGPayClient
{
    public function createOrder(array $payload): array
    {
        return $this->post('/api/order/create', $payload);
    }

    public function post(string $path, array $payload): array
    {
        $secret = (string) config('services.lgpay.secret_key');
        $payload['sign'] = LGPaySignature::make($payload, $secret);

        try {
            $response = Http::asForm()
                ->timeout(15)
                ->connectTimeout(5)
                ->post(rtrim((string) config('services.lgpay.api_base'), '/') . $path, $payload);
        } catch (ConnectionException|RequestException $e) {
            Log::warning('LG Pay request failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 0,
                'msg' => 'Payment gateway is not responding. Please try again.',
                'error_code' => 'gateway_unreachable',
            ];
        }

        $json = $response->json();
        if (!is_array($json)) {
            Log::warning('LG Pay invalid response', ['path' => $path, 'status' => $response->status()]);
            return ['status' => 0, 'msg' => 'Invalid gateway response'];
        }

        Log::info('LG Pay response', [
            'path' => $path,
            'status' => $response->status(),
            'gateway_status' => $json['status'] ?? null,
            'gateway_msg' => $json['msg'] ?? null,
        ]);

        return $json;
    }
}
