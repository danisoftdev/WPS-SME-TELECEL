<?php

namespace App\Http\Controllers;

use App\Models\BundleSubscription;
use App\Models\UserSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserSubscriptionController extends Controller
{
    public function index(): View
    {
        $subscriptions = auth()->user()->userSubscriptions()->with('bundleSubscription')->paginate(20);
        return view('subscriptions.index', compact('subscriptions'));
    }

    public function create(Request $request): View
    {
        $networks = ['Telecel', 'MTN', 'Airtel Tigo'];
        $selectedNetwork = $request->get('network', 'Telecel');

        $bundles = BundleSubscription::active()->network($selectedNetwork)->get();

        return view('subscriptions.create', compact('bundles', 'networks', 'selectedNetwork'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'bundle_subscription_id' => 'required|exists:bundle_subscriptions,id',
            'balance_gb' => 'required|numeric|min:0',
            'beneficiaries' => 'nullable|array',
            'beneficiaries.*' => 'string|max:20',
        ]);
        $bundle = BundleSubscription::findOrFail($request->bundle_subscription_id);
        $beneficiaries = $request->beneficiaries ?? [];
        if (count($beneficiaries) > $bundle->max_beneficiaries) {
            return back()->withErrors(['beneficiaries' => 'Max ' . $bundle->max_beneficiaries . ' beneficiaries allowed.']);
        }
        UserSubscription::create([
            'user_id' => auth()->id(),
            'bundle_subscription_id' => $bundle->id,
            'balance_gb' => $request->balance_gb,
            'beneficiaries' => $beneficiaries,
        ]);
        return redirect()->route('subscriptions.index')->with('success', 'Subscription added.');
    }

    public function edit(UserSubscription $subscription): View|RedirectResponse
    {
        if ($subscription->user_id !== auth()->id()) {
            abort(404);
        }
        $subscription->load('bundleSubscription');
        return view('subscriptions.edit', compact('subscription'));
    }

    public function update(Request $request, UserSubscription $subscription): RedirectResponse
    {
        if ($subscription->user_id !== auth()->id()) {
            abort(404);
        }
        $request->validate([
            'balance_gb' => 'required|numeric|min:0',
            'beneficiaries' => 'nullable|array',
            'beneficiaries.*' => 'string|max:20',
        ]);
        $bundle = $subscription->bundleSubscription;
        $beneficiaries = $request->beneficiaries ?? [];
        if (count($beneficiaries) > $bundle->max_beneficiaries) {
            return back()->withErrors(['beneficiaries' => 'Max ' . $bundle->max_beneficiaries . ' beneficiaries allowed.']);
        }
        $subscription->update(['balance_gb' => $request->balance_gb, 'beneficiaries' => $beneficiaries]);
        return redirect()->route('subscriptions.index')->with('success', 'Subscription updated.');
    }
}
