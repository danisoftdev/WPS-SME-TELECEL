@extends('layouts.app')

@section('content')
<h1 class="page-title">Super Admin Dashboard</h1>

<div class="card p-5 mb-8">
    <h2 class="section-title">Quick actions</h2>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-primary text-center text-sm py-2.5">Orders</a>
        <a href="{{ route('admin.wallets.index') }}" class="btn btn-secondary text-center text-sm py-2.5">Wallets</a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary text-center text-sm py-2.5">Users</a>
        <a href="{{ route('admin.bundles.index') }}" class="btn btn-secondary text-center text-sm py-2.5">Bundles</a>
        <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary text-center text-sm py-2.5">Notifications</a>
        <a href="{{ route('admin.passwordResets.index') }}" class="btn btn-secondary text-center text-sm py-2.5">Password resets</a>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary text-center text-sm py-2.5">Roles</a>
        <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary text-center text-sm py-2.5">Settings</a>
    </div>
</div>

<div class="grid gap-4 md:grid-cols-4 mb-8">
    <div class="stat-card">
        <p class="stat-label">Orders today</p>
        <p class="stat-value mt-0.5">{{ $stats['orders_today'] }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Pending</p>
        <p class="stat-value mt-0.5">{{ $stats['orders_pending'] }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Processing</p>
        <p class="stat-value mt-0.5">{{ $stats['orders_processing'] }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Users</p>
        <p class="stat-value mt-0.5">{{ $stats['users_count'] }}</p>
    </div>
</div>
<p><a href="{{ route('admin.orders.index') }}" class="link-sea font-medium">Manage orders →</a></p>
@endsection
