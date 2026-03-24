<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IgateWebhookController extends Controller
{
    public function __construct(protected OrderService $orderService) {}

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|max:64',
            'order_id' => 'nullable|integer|min:1',
            'external_reference' => 'nullable|string|max:128',
            'message' => 'nullable|string|max:1000',
            'refund_wallet' => 'nullable|boolean',
        ]);

        $orderId = $this->resolveOrderId($request);
        if ($orderId === null) {
            return response()->json(['error' => 'order_id or external_reference (wps:ID) required'], 422);
        }

        $order = Order::query()->find($orderId);
        if (! $order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $incoming = strtolower(trim($request->input('status')));
        $map = config('services.igate.webhook_status_map', []);
        $target = $map[$incoming] ?? null;
        if ($target === null || ! in_array($target, ['sent', 'failed'], true)) {
            return response()->json(['error' => 'Unknown or unmapped status'], 422);
        }

        $notes = $request->filled('message') ? 'iGate: '.$request->message : 'iGate webhook';

        if ($target === 'sent') {
            if ($order->status === Order::STATUS_SENT) {
                return response()->json(['ok' => true, 'message' => 'Already sent']);
            }
            if ($order->status !== Order::STATUS_PROCESSING) {
                return response()->json([
                    'error' => 'Order must be in processing state (current: '.$order->status.')',
                ], 409);
            }
            try {
                $this->orderService->updateStatus($order, Order::STATUS_SENT, $notes, null, false);
            } catch (\Throwable $e) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return response()->json(['ok' => true, 'order_id' => $order->id, 'status' => Order::STATUS_SENT]);
        }

        // failed
        if ($order->status === Order::STATUS_FAILED) {
            return response()->json(['ok' => true, 'message' => 'Already failed']);
        }
        if ($order->status === Order::STATUS_SENT) {
            return response()->json(['error' => 'Order already sent'], 409);
        }
        if ($order->status !== Order::STATUS_PROCESSING) {
            return response()->json([
                'error' => 'Order must be in processing state (current: '.$order->status.')',
            ], 409);
        }

        try {
            $this->orderService->updateStatus(
                $order,
                Order::STATUS_FAILED,
                $notes,
                null,
                $request->boolean('refund_wallet')
            );
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json(['ok' => true, 'order_id' => $order->id, 'status' => Order::STATUS_FAILED]);
    }

    private function resolveOrderId(Request $request): ?int
    {
        if ($request->filled('order_id')) {
            return (int) $request->input('order_id');
        }
        $ref = $request->input('external_reference');
        if (is_string($ref) && preg_match('/^wps:(\d+)$/i', $ref, $m)) {
            return (int) $m[1];
        }

        return null;
    }
}
