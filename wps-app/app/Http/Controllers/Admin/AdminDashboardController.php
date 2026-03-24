<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_all_orders');
    }

    public function index(): View
    {
        $stats = [
            'orders_today' => Order::whereDate('created_at', today())->count(),
            'orders_pending' => Order::where('status', Order::STATUS_PENDING)->count(),
            'orders_processing' => Order::where('status', Order::STATUS_PROCESSING)->count(),
            'users_count' => User::count(),
        ];
        return view('admin.dashboard', compact('stats'));
    }
}
