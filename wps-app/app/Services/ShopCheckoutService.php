<?php

namespace App\Services;

use App\Models\ShopCheckout;
use App\Models\Store;
use App\Models\StoreBundlePrice;
use App\Models\SystemSetting;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShopCheckoutService
{
    public function createPendingCheckout(Store $store, int $bundleSubscriptionId, string $customerPhone): ShopCheckout
    {
        if (! $store->is_active) {
            abort(404);
        }

        $priceRow = StoreBundlePrice::query()
            ->where('store_id', $store->id)
            ->where('bundle_subscription_id', $bundleSubscriptionId)
            ->with('bundleSubscription')
            ->first();

        if (! $priceRow) {
            throw ValidationException::withMessages([
                'bundle_subscription_id' => ['Product not found for this store.'],
            ]);
        }

        $bundle = $priceRow->bundleSubscription;
        if (! $bundle || ! $bundle->is_active) {
            throw ValidationException::withMessages([
                'bundle_subscription_id' => ['This product is not available.'],
            ]);
        }

        $base = (float) $bundle->amount;
        $sell = (float) $priceRow->selling_price;
        if ($sell <= $base) {
            throw ValidationException::withMessages([
                'bundle_subscription_id' => ['Invalid store price.'],
            ]);
        }

        $feePercent = (float) SystemSetting::get('shop_platform_fee_percent', 0);
        $total = round($sell, 2);
        $fee = round($total * max(0, $feePercent) / 100, 2);
        $net = round($total - $fee, 2);

        $reference = 'SHOP-'.Str::upper(Str::random(12)).'-'.time();

        return ShopCheckout::create([
            'store_id' => $store->id,
            'bundle_subscription_id' => $bundle->id,
            'selling_price' => $sell,
            'customer_phone' => $customerPhone,
            'amount_total' => $total,
            'platform_fee' => $fee,
            'net_to_merchant' => $net,
            'reference' => $reference,
            'status' => 'pending',
        ]);
    }
}
