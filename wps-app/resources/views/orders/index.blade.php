@extends('layouts.app')

@section('content')
<h1 class="page-title">My Orders</h1>
<form method="GET" class="mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
        <select name="status" class="block ring-sea min-w-[140px]">
            <option value="">All statuses</option>
            @foreach(['pending','processing','sent','failed','refunded'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-secondary py-2 text-sm">Filter</button>
</form>
<div class="card overflow-hidden overflow-x-auto">
    <table class="min-w-full table-premium">
        <thead>
            <tr>
                <th>ID</th>
                <th>Phone</th>
                <th>Plan</th>
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
                    <td>{{ $o->resellerPlan->data_size_gb ?? '-' }} GB</td>
                    <td>GH¢ {{ number_format($o->amount, 2) }}</td>
                    <td><span class="badge badge-neutral">{{ $o->status }}</span></td>
                    <td class="text-slate-500 text-sm">{{ $o->created_at->format('M d, H:i') }}</td>
                    <td><a href="{{ route('orders.show', $o) }}" class="link-sea text-sm font-medium">View</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-slate-500 text-center">No orders.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $orders->links() }}</div>
@endsection
