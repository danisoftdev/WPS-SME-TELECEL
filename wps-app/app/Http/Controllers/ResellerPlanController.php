<?php

namespace App\Http\Controllers;

use App\Models\ResellerPlan;
use App\Models\UserSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResellerPlanController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage_reseller_plans');
    }

    public function index(): View
    {
        $plans = auth()->user()->resellerPlans()->with('userSubscription.bundleSubscription')->latest()->paginate(20);
        return view('plans.index', compact('plans'));
    }

    public function create(Request $request): View
    {
        $networks = ['Telecel', 'MTN', 'Airtel Tigo'];
        $selectedNetwork = $request->get('network', 'Telecel');

        $subscriptions = auth()->user()
            ->userSubscriptions()
            ->with('bundleSubscription')
            ->get()
            ->filter(fn ($s) => ($s->bundleSubscription?->network ?? 'Telecel') === $selectedNetwork)
            ->values();

        return view('plans.create', compact('subscriptions', 'networks', 'selectedNetwork'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'user_subscription_id' => 'required|exists:user_subscriptions,id',
            'data_size_gb' => 'required|numeric|min:0.01',
            'price' => 'required|numeric|min:0',
            'available_units' => 'required|integer|min:0',
        ]);
        $sub = UserSubscription::where('user_id', auth()->id())->findOrFail($request->user_subscription_id);
        ResellerPlan::create([
            'user_id' => auth()->id(),
            'user_subscription_id' => $sub->id,
            'data_size_gb' => $request->data_size_gb,
            'price' => $request->price,
            'available_units' => $request->available_units,
            'is_active' => true,
        ]);
        return redirect()->route('plans.index')->with('success', 'Plan created.');
    }

    public function edit(ResellerPlan $plan): View|RedirectResponse
    {
        if ($plan->user_id !== auth()->id()) {
            abort(404);
        }
        $plan->load('userSubscription.bundleSubscription');
        return view('plans.edit', compact('plan'));
    }

    public function update(Request $request, ResellerPlan $plan): RedirectResponse
    {
        if ($plan->user_id !== auth()->id()) {
            abort(404);
        }
        $request->validate([
            'price' => 'required|numeric|min:0',
            'available_units' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);
        $plan->update([
            'price' => $request->price,
            'available_units' => $request->available_units,
            'is_active' => $request->boolean('is_active', true),
        ]);
        return redirect()->route('plans.index')->with('success', 'Plan updated.');
    }

    public function destroy(ResellerPlan $plan): RedirectResponse
    {
        if ($plan->user_id !== auth()->id()) {
            abort(404);
        }
        $plan->delete();
        return redirect()->route('plans.index')->with('success', 'Plan deleted.');
    }
}
