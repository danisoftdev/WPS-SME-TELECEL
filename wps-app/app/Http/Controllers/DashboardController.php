<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $wallet = app(\App\Services\WalletService::class)->getOrCreateWallet($user);
        $orders = $user->orders()->with('resellerPlan')->orderByDesc('created_at')->limit(10)->get();
        $pendingCount = $user->orders()->where('status', Order::STATUS_PENDING)->count();
        return view('dashboard', [
            'wallet' => $wallet,
            'orders' => $orders,
            'pendingCount' => $pendingCount,
        ]);
    }
}
