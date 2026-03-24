@extends('layouts.app')

@section('content')
<h1 class="page-title">All Orders</h1>
<form method="GET" class="mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
        <select name="status" class="block rounded-xl border border-slate-300 px-3 py-2 text-sm ring-sea">
            <option value="">All</option>
            @foreach(['pending','processing','sent','failed','refunded'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">From date</label>
        <input type="date" name="from_date" value="{{ request('from_date') }}" class="block rounded-xl border border-slate-300 px-3 py-2 text-sm ring-sea">
    </div>
    <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">To date</label>
        <input type="date" name="to_date" value="{{ request('to_date') }}" class="block rounded-xl border border-slate-300 px-3 py-2 text-sm ring-sea">
    </div>
    <button type="submit" class="btn btn-secondary py-2 text-sm">Filter</button>
</form>
<div class="card overflow-hidden overflow-x-auto">
    <table class="min-w-full table-premium">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Phone</th>
                <th>Amount</th>
                <th>Store</th>
                <th>Status</th>
                <th>Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $o)
                <tr>
                    <td>{{ $o->id }}</td>
                    <td>{{ $o->user->name ?? '-' }}</td>
                    <td>{{ $o->phone_number }}</td>
                    <td>GH¢ {{ number_format($o->amount, 2) }}</td>
                    <td class="text-sm text-slate-600">{{ $o->store?->name ?? '—' }}</td>
                    <td><span class="badge badge-neutral">{{ $o->status }}</span></td>
                    <td class="text-slate-500 text-sm">{{ $o->created_at->format('M d, H:i') }}</td>
                    <td><a href="{{ route('admin.orders.show', $o) }}" class="link-sea text-sm font-medium">View</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-4 py-8 text-slate-500 text-center">No orders.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $orders->links() }}</div>
@endsection
