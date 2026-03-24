<?php

namespace App\Http\Controllers;

use App\Models\BundleSubscription;
use App\Models\Store;
use App\Models\StoreBundlePrice;
use App\Rules\SellingPriceAboveBase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Illuminate\View\View;

class StoreBundlePricingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit(Store $store): View
    {
        $this->authorizeOwner($store);

        $bundles = BundleSubscription::query()->where('is_active', true)->orderBy('network')->orderBy('name')->get();
        $prices = $store->bundlePrices()->get()->keyBy('bundle_subscription_id');

        return view('stores.pricing', compact('store', 'bundles', 'prices'));
    }

    public function update(Request $request, Store $store): RedirectResponse
    {
        $this->authorizeOwner($store);

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
            return back()->withInput()->withErrors($bag);
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

        return redirect()->route('stores.pricing.edit', $store)->with('success', 'Store catalog prices saved.');
    }

    private function authorizeOwner(Store $store): void
    {
        if ((int) $store->user_id !== (int) auth()->id()) {
            abort(403);
        }
    }
}
