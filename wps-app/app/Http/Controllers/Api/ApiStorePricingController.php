<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BundleSubscription;
use App\Models\Store;
use App\Models\StoreBundlePrice;
use App\Rules\SellingPriceAboveBase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;

class ApiStorePricingController extends Controller
{
    public function show(Request $request, Store $store): JsonResponse
    {
        $this->authorizeOwner($request, $store);

        $bundles = BundleSubscription::query()->where('is_active', true)->orderBy('network')->orderBy('name')->get();
        $prices = $store->bundlePrices()->get()->keyBy('bundle_subscription_id');

        $data = $bundles->map(function (BundleSubscription $b) use ($prices) {
            $row = $prices->get($b->id);

            return [
                'bundle_subscription_id' => $b->id,
                'name' => $b->name,
                'network' => $b->network,
                'base_amount' => (float) $b->amount,
                'selling_price' => $row ? (float) $row->selling_price : null,
            ];
        })->values();

        return response()->json(['data' => $data]);
    }

    public function update(Request $request, Store $store): JsonResponse
    {
        $this->authorizeOwner($request, $store);

        $request->validate([
            'prices' => 'required|array',
            'prices.*' => 'nullable|numeric|min:0',
        ]);

        $bundles = BundleSubscription::query()->where('is_active', true)->get()->keyBy('id');
        $bag = new MessageBag;

        foreach ($request->input('prices', []) as $bid => $raw) {
            if ($raw === null || $raw === '') {
                continue;
            }
            $bundle = $bundles->get((int) $bid);
            if (! $bundle) {
                continue;
            }
            $v = Validator::make(
                ['p' => $raw],
                ['p' => ['required', 'numeric', 'min:0', new SellingPriceAboveBase($bundle)]]
            );
            if ($v->fails()) {
                $bag->merge($v->errors());
            }
        }

        if ($bag->isNotEmpty()) {
            throw ValidationException::withMessages($bag->getMessages());
        }

        foreach ($request->input('prices', []) as $bid => $raw) {
            if ($raw === null || $raw === '') {
                StoreBundlePrice::query()
                    ->where('store_id', $store->id)
                    ->where('bundle_subscription_id', (int) $bid)
                    ->delete();

                continue;
            }
            $bundle = $bundles->get((int) $bid);
            if (! $bundle) {
                continue;
            }

            StoreBundlePrice::query()->updateOrCreate(
                [
                    'store_id' => $store->id,
                    'bundle_subscription_id' => (int) $bid,
                ],
                ['selling_price' => $raw]
            );
        }

        return response()->json(['message' => 'Store catalog prices saved.']);
    }

    private function authorizeOwner(Request $request, Store $store): void
    {
        if ((int) $store->user_id !== (int) $request->user()->id) {
            abort(404);
        }
    }
}
