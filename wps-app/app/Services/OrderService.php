<?php

namespace App\Services;

use App\Jobs\DispatchOrderToIgate;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\ResellerPlan;
use App\Models\SubAgentBundlePrice;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    /**
     * Place order: debit wallet, create order (PENDING), record history. Validates limits and stock.
     */
    public function placeOrder(
        User $user,
        string $phoneNumber,
        int $resellerPlanId,
        bool $confirmationChecked,
        ?string $network = null
    ): Order
    {
        if (!$confirmationChecked) {
            throw new \RuntimeException('You must confirm the order and no-refund notice.');
        }

        $plan = ResellerPlan::where('user_id', $user->id)
            ->where('is_active', true)
            ->findOrFail($resellerPlanId);

        if ($network !== null) {
            $plan->load('userSubscription.bundleSubscription');
            $planNetwork = $plan->userSubscription?->bundleSubscription?->network ?? 'Telecel';
            if ($planNetwork !== $network) {
                throw new \RuntimeException('Selected network does not match the chosen data plan.');
            }
        }

        if ($plan->available_units < 1) {
            throw new \RuntimeException('Insufficient stock for this plan.');
        }

        $maxPending = (int) SystemSetting::get('max_pending_orders_per_user', 10);
        $userLimit = $user->daily_order_limit ?? $maxPending;
        $pendingCount = $user->orders()->where('status', Order::STATUS_PENDING)->count();
        if ($pendingCount >= $userLimit) {
            throw new \RuntimeException("Maximum pending orders ({$userLimit}) reached.");
        }

        $processingSame = Order::where('user_id', $user->id)
            ->where('phone_number', $phoneNumber)
            ->where('reseller_plan_id', $resellerPlanId)
            ->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_PROCESSING])
            ->exists();
        if ($processingSame) {
            throw new \RuntimeException('A similar order is already pending or processing.');
        }

        if ($user->is_frozen) {
            throw new \RuntimeException('Your account is frozen.');
        }

        $plan->loadMissing('userSubscription.bundleSubscription');
        $bundleSubscription = $plan->userSubscription?->bundleSubscription;
        $bundleSubscriptionId = $plan->userSubscription?->bundle_subscription_id;

        $debitAmount = (float) $plan->price;
        $storeId = null;
        $subAgentUserId = null;

        if ($user->hasRole('SubAgent') && $user->store_id && $bundleSubscriptionId) {
            $storePrice = SubAgentBundlePrice::query()
                ->where('user_id', $user->id)
                ->where('store_id', $user->store_id)
                ->where('bundle_subscription_id', $bundleSubscriptionId)
                ->first();

            if (! $storePrice) {
                throw new \RuntimeException('Set your store resale price for this product under Store prices before placing an order.');
            }

            $base = (float) ($bundleSubscription?->amount ?? 0);
            $sell = (float) $storePrice->selling_price;
            if ($sell <= $base) {
                throw new \RuntimeException('Your store price must be above the platform base price. Update Store prices.');
            }

            $debitAmount = $sell;
            $storeId = $user->store_id;
            $subAgentUserId = $user->id;
        }

        return DB::transaction(function () use ($user, $phoneNumber, $plan, $debitAmount, $storeId, $subAgentUserId) {
            $note = 'Order: '.$phoneNumber.' - '.$plan->data_size_gb.'GB';
            if ($subAgentUserId !== null) {
                $note .= ' (sub-agent store)';
            }

            $this->walletService->debit(
                $user,
                $debitAmount,
                'order',
                null,
                $note,
                null
            );

            $order = Order::create([
                'user_id' => $user->id,
                'store_id' => $storeId,
                'sub_agent_user_id' => $subAgentUserId,
                'phone_number' => $phoneNumber,
                'reseller_plan_id' => $plan->id,
                'amount' => $debitAmount,
                'status' => Order::STATUS_PENDING,
            ]);

            $plan->decrement('available_units');

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => Order::STATUS_PENDING,
                'notes' => 'Order placed.',
                'changed_by' => $user->id,
                'created_at' => now(),
            ]);

            return $order->fresh();
        });
    }

    /**
     * Update order status (admin only). Enforces lifecycle; on FAILED can trigger refund.
     */
    public function updateStatus(
        Order $order,
        string $newStatus,
        ?string $notes = null,
        ?User $changedBy = null,
        bool $refundOnFailed = false
    ): Order {
        if ($order->isFinal()) {
            throw new \RuntimeException('Order is in a final state and cannot be updated.');
        }

        $allowed = [
            Order::STATUS_PENDING => [Order::STATUS_PROCESSING],
            Order::STATUS_PROCESSING => [Order::STATUS_SENT, Order::STATUS_FAILED],
            Order::STATUS_FAILED => [Order::STATUS_REFUNDED],
        ];
        $current = $order->status;
        if (!isset($allowed[$current]) || !in_array($newStatus, $allowed[$current], true)) {
            throw new \RuntimeException("Invalid status transition from {$current} to {$newStatus}.");
        }

        $fresh = DB::transaction(function () use ($order, $newStatus, $notes, $changedBy, $refundOnFailed) {
            $oldStatus = $order->status;

            if ($newStatus === Order::STATUS_REFUNDED || ($newStatus === Order::STATUS_FAILED && $refundOnFailed)) {
                $this->walletService->refund(
                    $order->user,
                    (float) $order->amount,
                    'order',
                    $order->id,
                    'Refund for order #' . $order->id,
                    $changedBy
                );
            }
            if ($newStatus === Order::STATUS_REFUNDED || $newStatus === Order::STATUS_FAILED) {
                $order->resellerPlan->increment('available_units');
            }

            $order->update(['status' => $newStatus]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'notes' => $notes,
                'changed_by' => $changedBy?->id,
                'created_at' => now(),
            ]);

            return $order->fresh();
        });

        if ($newStatus === Order::STATUS_PROCESSING && config('services.igate.enabled')) {
            DispatchOrderToIgate::dispatch($fresh->id)->afterResponse();
        }

        return $fresh;
    }

    /**
     * Bulk update status for multiple orders (admin).
     *
     * @param array<int, string> $orderIdToStatus [orderId => newStatus]
     */
    public function bulkUpdateStatus(array $orderIdToStatus, ?string $notes = null, ?User $changedBy = null): void
    {
        foreach ($orderIdToStatus as $orderId => $newStatus) {
            $order = Order::find($orderId);
            if ($order) {
                try {
                    $this->updateStatus($order, $newStatus, $notes, $changedBy, false);
                } catch (\Throwable) {
                    // skip invalid transitions
                }
            }
        }
    }
}
