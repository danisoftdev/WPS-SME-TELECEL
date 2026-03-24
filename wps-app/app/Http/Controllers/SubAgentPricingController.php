<?php

namespace App\Http\Controllers;

use App\Models\BundleSubscription;
use App\Models\SubAgentBundlePrice;
use App\Rules\SellingPriceAboveBase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Illuminate\View\View;

class SubAgentPricingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:set_sub_agent_prices');
    }

    public function index(): View|RedirectResponse
    {
        $user = auth()->user();
        if (! $user->store_id) {
            return redirect()->route('dashboard')->with('info', 'Join a store via an invite link to set your resale prices.');
        }

        $bundles = BundleSubscription::query()->where('is_active', true)->orderBy('network')->orderBy('name')->get();
        $prices = $user->subAgentBundlePrices()
            ->where('store_id', $user->store_id)
            ->get()
            ->keyBy('bundle_subscription_id');

        return view('sub_agent_pricing.index', compact('bundles', 'prices'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->store_id) {
            abort(403);
        }

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
                SubAgentBundlePrice::query()
                    ->where('user_id', $user->id)
                    ->where('store_id', $user->store_id)
                    ->where('bundle_subscription_id', (int) $bid)
                    ->delete();

                continue;
            }
            $bundle = $bundles->get((int) $bid);
            if (! $bundle) {
                continue;
            }

            SubAgentBundlePrice::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'bundle_subscription_id' => (int) $bid,
                    'store_id' => $user->store_id,
                ],
                ['selling_price' => $raw]
            );
        }

        return redirect()->route('sub-agent.pricing.index')->with('success', 'Store prices saved.');
    }
}
