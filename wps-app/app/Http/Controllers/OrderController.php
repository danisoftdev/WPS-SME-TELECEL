<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService)
    {
        $this->middleware('permission:place_orders');
    }

    public function index(Request $request): View
    {
        $query = auth()->user()->orders()->with('resellerPlan')->orderByDesc('created_at');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $orders = $query->paginate(20)->withQueryString();
        return view('orders.index', compact('orders'));
    }

    public function create(Request $request): View
    {
        $networks = ['Telecel', 'MTN', 'Airtel Tigo'];
        $selectedNetwork = $request->get('network', 'Telecel');

        $plans = auth()->user()
            ->resellerPlans()
            ->where('is_active', true)
            ->with('userSubscription.bundleSubscription')
            ->get()
            ->filter(fn ($p) => ($p->userSubscription?->bundleSubscription?->network ?? 'Telecel') === $selectedNetwork)
            ->values();

        $lastOrder = auth()->user()->orders()->with('resellerPlan')->orderByDesc('created_at')->first();

        $subAgentPriceMap = [];
        $user = auth()->user();
        if ($user->hasRole('SubAgent') && $user->store_id) {
            $subAgentPriceMap = $user->subAgentBundlePrices()
                ->where('store_id', $user->store_id)
                ->pluck('selling_price', 'bundle_subscription_id')
                ->map(fn ($v) => (float) $v)
                ->all();
        }

        return view('orders.create', compact('plans', 'lastOrder', 'networks', 'selectedNetwork', 'subAgentPriceMap'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'phone_number' => 'required|string|max:20',
            'reseller_plan_id' => 'required|integer|exists:reseller_plans,id',
            'confirmation_checked' => 'required|accepted',
            'network' => 'nullable|string|in:Telecel,MTN,Airtel Tigo',
        ]);
        try {
            $this->orderService->placeOrder(
                $request->user(),
                $request->phone_number,
                (int) $request->reseller_plan_id,
                true,
                $request->filled('network') ? $request->network : null
            );
        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors(['order' => $e->getMessage()]);
        }

        return redirect()->route('orders.index')->with('success', 'Order placed successfully.');
    }

    public function show(Order $order): View|RedirectResponse
    {
        if ($order->user_id !== auth()->id()) {
            abort(404);
        }
        $order->load(['resellerPlan', 'statusHistory', 'store', 'subAgent']);
        return view('orders.show', compact('order'));
    }

    public function repeatLast(): RedirectResponse
    {
        $last = auth()->user()->orders()->with('resellerPlan')->orderByDesc('created_at')->first();
        if (!$last) {
            return redirect()->route('orders.create')->with('info', 'No previous order to repeat.');
        }

        $network = $last->resellerPlan?->userSubscription?->bundleSubscription?->network ?? 'Telecel';
        return redirect()->route('orders.create', [
            'phone_number' => $last->phone_number,
            'reseller_plan_id' => $last->reseller_plan_id,
            'network' => $network,
        ]);
    }
}
