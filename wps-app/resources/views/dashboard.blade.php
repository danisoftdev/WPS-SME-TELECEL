@extends('layouts.app')

@section('content')
<h1 class="page-title">Dashboard</h1>

<div class="card p-5 mb-8">
    <h2 class="section-title">Quick actions</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        <a href="{{ route('orders.create') }}" class="btn btn-primary text-center text-sm py-2.5">Place order</a>
        <a href="{{ route('orders.index') }}" class="btn btn-secondary text-center text-sm py-2.5">My orders</a>
        <a href="{{ route('wallet.show') }}" class="btn btn-secondary text-center text-sm py-2.5">Wallet</a>
        <a href="{{ route('plans.index') }}" class="btn btn-secondary text-center text-sm py-2.5">My plans</a>
        <a href="{{ route('subscriptions.index') }}" class="btn btn-secondary text-center text-sm py-2.5">Subscriptions</a>
        <a href="{{ route('orders.repeat') }}" class="btn btn-secondary text-center text-sm py-2.5">Repeat last</a>
    </div>
</div>

<div class="grid gap-4 md:grid-cols-3 mb-8">
    <div class="stat-card">
        <p class="stat-label">Wallet Balance</p>
        <p class="stat-value mt-0.5">GH¢ {{ number_format($wallet->balance ?? 0, 2) }}</p>
        @if($wallet && $wallet->is_frozen)
            <p class="text-sm text-amber-600 mt-1 font-medium">Frozen</p>
        @endif
    </div>
    <div class="stat-card">
        <p class="stat-label">Pending Orders</p>
        <p class="stat-value mt-0.5">{{ $pendingCount }}</p>
    </div>
    <div class="stat-card flex flex-col justify-center">
        <a href="{{ route('orders.create') }}" class="link-sea font-semibold">Place new order →</a>
        <a href="{{ route('orders.repeat') }}" class="text-sm text-slate-500 mt-1">Repeat last order</a>
    </div>
</div>

<h2 class="section-title">Recent Orders</h2>
<div class="card overflow-hidden">
    <table class="min-w-full table-premium">
        <thead>
            <tr>
                <th>ID</th>
                <th>Phone</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $o)
                <tr>
                    <td>{{ $o->id }}</td>
                    <td>{{ $o->phone_number }}</td>
                    <td>GH¢ {{ number_format($o->amount, 2) }}</td>
                    <td><span class="badge badge-neutral">{{ $o->status }}</span></td>
                    <td class="text-slate-500 text-sm">{{ $o->created_at->format('M d, H:i') }}</td>
                    <td><a href="{{ route('orders.show', $o) }}" class="link-sea text-sm font-medium">View</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-slate-500 text-center">No orders yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<p class="mt-4"><a href="{{ route('orders.index') }}" class="link-sea font-medium">View all orders</a></p>
@endsection
