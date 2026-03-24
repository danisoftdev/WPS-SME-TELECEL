<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\IgateOrderClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchOrderToIgate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $orderId) {}

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(IgateOrderClient $client): void
    {
        if (! config('services.igate.enabled')) {
            return;
        }

        $order = Order::query()->find($this->orderId);
        if (! $order || $order->status !== Order::STATUS_PROCESSING) {
            return;
        }

        if ($order->igate_submitted_at !== null) {
            return;
        }

        $result = $client->submitOrder($order);

        if ($result['ok']) {
            $order->update([
                'igate_submitted_at' => now(),
                'igate_last_http_code' => $result['status'],
                'igate_last_error' => null,
            ]);

            return;
        }

        $order->update([
            'igate_last_http_code' => $result['status'],
            'igate_last_error' => $result['error'] ?? ($result['body'] ?? 'Unknown error'),
        ]);

        throw new \RuntimeException('iGate submission failed for order #'.$this->orderId);
    }
}
