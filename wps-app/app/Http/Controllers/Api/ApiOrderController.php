<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiOrderController extends Controller
{
    public function __construct(protected OrderService $orderService) {}

    public function index(Request $request): JsonResponse
    {
        $query = $request->user()
            ->orders()
            ->with(['resellerPlan.userSubscription.bundleSubscription', 'store', 'subAgent'])
            ->orderByDesc('created_at');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        $orders = $query->limit($request->integer('limit', 50))->get()->map(fn ($o) => $this->formatOrder($o));
        return response()->json(['data' => $orders]);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            abort(404);
        }
        $order->load(['resellerPlan.userSubscription.bundleSubscription', 'statusHistory', 'store', 'subAgent']);
        return response()->json($this->formatOrder($order, true));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number' => 'required|string|max:20',
            'reseller_plan_id' => 'required|integer|exists:reseller_plans,id',
            'confirmation_checked' => 'required|accepted',
            'network' => 'nullable|string|in:Telecel,MTN,Airtel Tigo',
        ]);

        $order = $this->orderService->placeOrder(
            $request->user(),
            $request->phone_number,
            (int) $request->reseller_plan_id,
            true,
            $request->filled('network') ? $request->network : null
        );
        $order->load(['resellerPlan.userSubscription.bundleSubscription', 'store', 'subAgent']);

        return response()->json([
            'message' => 'Order placed.',
            'order' => $this->formatOrder($order),
        ], 201);
    }

    private function formatOrder(Order $order, bool $withHistory = false): array
    {
        $order->loadMissing('store', 'subAgent');

        $data = [
            'id' => $order->id,
            'phone_number' => $order->phone_number,
            'amount' => (float) $order->amount,
            'status' => $order->status,
            'created_at' => $order->created_at->toIso8601String(),
            'network' => $order->resellerPlan?->userSubscription?->bundleSubscription?->network ?? 'Telecel',
            'store_id' => $order->store_id,
            'sub_agent_user_id' => $order->sub_agent_user_id,
            'store_name' => $order->store?->name,
            'sub_agent' => $order->subAgent ? [
                'id' => $order->subAgent->id,
                'name' => $order->subAgent->name,
                'email' => $order->subAgent->email,
            ] : null,
            'plan' => $order->resellerPlan ? [
                'data_size_gb' => (float) $order->resellerPlan->data_size_gb,
                'price' => (float) $order->resellerPlan->price,
            ] : null,
        ];
        if ($withHistory && $order->relationLoaded('statusHistory')) {
            $data['status_history'] = $order->statusHistory->map(fn ($h) => [
                'from_status' => $h->from_status,
                'to_status' => $h->to_status,
                'notes' => $h->notes,
                'created_at' => $h->created_at->toIso8601String(),
            ])->values();
        }
        return $data;
    }
}
