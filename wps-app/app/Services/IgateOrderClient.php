<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IgateOrderClient
{
    /**
     * POST order payload to iGate (or compatible HTTP API).
     *
     * @return array{ok: bool, status: int|null, body: string|null, error: string|null}
     */
    public function submitOrder(Order $order): array
    {
        if (! config('services.igate.enabled')) {
            return ['ok' => false, 'status' => null, 'body' => null, 'error' => 'iGate is disabled.'];
        }

        $base = (string) config('services.igate.base_url', '');
        if ($base === '') {
            return ['ok' => false, 'status' => null, 'body' => null, 'error' => 'IGATE_BASE_URL is not set.'];
        }

        $path = ltrim((string) config('services.igate.orders_endpoint', 'api/orders'), '/');
        $url = rtrim($base, '/').'/'.$path;
        $payload = $this->buildPayload($order);

        try {
            $headers = [
                'Accept' => 'application/json',
                'X-Idempotency-Key' => 'wps-order-'.$order->id,
            ];
            $token = config('services.igate.token');
            if (is_string($token) && $token !== '') {
                $headers['Authorization'] = 'Bearer '.$token;
            }

            $pending = Http::timeout((int) config('services.igate.timeout', 15))
                ->withHeaders($headers);

            $response = $pending->asJson()->post($url, $payload);
            $ok = $response->successful();
            $body = $response->body();

            if (! $ok) {
                Log::warning('iGate order submission failed', [
                    'order_id' => $order->id,
                    'status' => $response->status(),
                    'body' => mb_substr($body, 0, 2000),
                ]);
            }

            return [
                'ok' => $ok,
                'status' => $response->status(),
                'body' => mb_substr($body, 0, 5000),
                'error' => $ok ? null : 'HTTP '.$response->status(),
            ];
        } catch (\Throwable $e) {
            Log::error('iGate order submission exception', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'status' => null,
                'body' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function buildPayload(Order $order): array
    {
        $order->loadMissing(['resellerPlan.userSubscription.bundleSubscription', 'user', 'store', 'subAgent']);

        $bundle = $order->resellerPlan?->userSubscription?->bundleSubscription;

        return [
            'external_reference' => 'wps:'.$order->id,
            'order_id' => $order->id,
            'phone_number' => $order->phone_number,
            'amount' => (float) $order->amount,
            'data_size_gb' => $order->resellerPlan ? (float) $order->resellerPlan->data_size_gb : null,
            'network' => $bundle?->network,
            'bundle_name' => $bundle?->name,
            'bundle_subscription_id' => $bundle?->id,
            'reseller_plan_id' => $order->reseller_plan_id,
            'user' => [
                'id' => $order->user_id,
                'email' => $order->user?->email,
                'name' => $order->user?->name,
            ],
            'store_id' => $order->store_id,
            'store_name' => $order->store?->name,
            'sub_agent_user_id' => $order->sub_agent_user_id,
            'sub_agent_email' => $order->subAgent?->email,
            'status' => $order->status,
            'created_at' => $order->created_at?->toIso8601String(),
        ];
    }
}
