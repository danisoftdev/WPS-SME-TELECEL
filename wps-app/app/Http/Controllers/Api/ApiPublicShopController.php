<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\PaystackGatewayConfig;
use App\Support\ShopProductNetworks;
use App\Services\ShopCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiPublicShopController extends Controller
{
    public function __construct(protected ShopCheckoutService $shopCheckoutService) {}

    public function catalog(string $slug): JsonResponse
    {
        $store = Store::query()->where('slug', $slug)->where('is_active', true)->firstOrFail();
        $store->load(['bundlePrices.bundleSubscription']);

        $lines = $store->bundlePrices->filter(fn ($p) => $p->bundleSubscription && $p->bundleSubscription->is_active)->values();

        [, $networksInShop] = ShopProductNetworks::groupByNetwork($lines);
        $networksInShop = $networksInShop->all();

        $data = $lines->map(function ($row) use ($store) {
            $b = $row->bundleSubscription;

            return [
                'bundle_subscription_id' => $b->id,
                'name' => $b->name,
                'network' => $b->network,
                'total_data_gb' => (float) $b->total_data_gb,
                'price' => (float) $row->selling_price,
                'store_slug' => $store->slug,
            ];
        });

        return response()->json([
            'store' => [
                'name' => $store->name,
                'slug' => $store->slug,
                'description' => $store->description,
                'whatsapp_phone' => $store->whatsapp_phone,
                'contact_email' => $store->contact_email,
                'location' => $store->location,
                'logo_url' => $store->seoOgImageUrl(),
            ],
            'networks' => $networksInShop,
            'products' => $data,
        ]);
    }

    public function checkout(Request $request, string $slug): JsonResponse
    {
        $store = Store::query()->where('slug', $slug)->where('is_active', true)->firstOrFail();

        $request->validate([
            'bundle_subscription_id' => 'required|integer|exists:bundle_subscriptions,id',
            'customer_phone' => 'required|string|max:32',
        ]);

        $checkout = $this->shopCheckoutService->createPendingCheckout(
            $store,
            $request->integer('bundle_subscription_id'),
            $request->customer_phone
        );

        return response()->json([
            'message' => 'Checkout created. Open pay_url to pay with Paystack.',
            'reference' => $checkout->reference,
            'amount_total' => (float) $checkout->amount_total,
            'currency' => PaystackGatewayConfig::currency(),
            'platform_fee' => (float) $checkout->platform_fee,
            'net_to_merchant' => (float) $checkout->net_to_merchant,
            'pay_url' => route('shop.checkout.pay', ['reference' => $checkout->reference]),
        ], 201);
    }
}
