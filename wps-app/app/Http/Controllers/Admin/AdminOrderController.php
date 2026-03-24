<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    public function __construct(protected OrderService $orderService)
    {
        $this->middleware('permission:manage_orders|view_all_orders');
    }

    public function index(Request $request): View
    {
        $query = Order::with(['user', 'resellerPlan', 'store'])->orderByDesc('created_at');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        $orders = $query->paginate(20)->withQueryString();
        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'resellerPlan.userSubscription.bundleSubscription', 'statusHistory.changedBy', 'store', 'subAgent']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        if (!auth()->user()->can('update_order_status')) {
            abort(403);
        }
        $request->validate([
            'status' => 'required|in:processing,sent,failed,refunded',
            'notes' => 'nullable|string|max:500',
            'refund_on_failed' => 'nullable|boolean',
        ]);
        $this->orderService->updateStatus(
            $order,
            $request->status,
            $request->notes,
            $request->user(),
            $request->boolean('refund_on_failed')
        );
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Status updated.']);
        }
        return redirect()->route('admin.orders.show', $order)->with('success', 'Status updated.');
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'integer|exists:orders,id',
            'status' => 'required|in:processing,sent,failed,refunded',
            'notes' => 'nullable|string|max:500',
        ]);
        $orderIdToStatus = array_fill_keys($request->order_ids, $request->status);
        $this->orderService->bulkUpdateStatus($orderIdToStatus, $request->notes, $request->user());
        return back()->with('success', 'Orders updated.');
    }

    public function updateNotes(Request $request, Order $order): RedirectResponse
    {
        $request->validate(['internal_notes' => 'nullable|string|max:1000']);
        $order->update(['internal_notes' => $request->internal_notes]);
        return back()->with('success', 'Notes updated.');
    }
}
