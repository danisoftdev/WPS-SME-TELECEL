@extends('layouts.app')

@section('content')
<h1 class="page-title">Withdrawal requests</h1>
<form method="GET" class="mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
        <select name="status" class="block rounded-xl border border-slate-300 px-3 py-2 text-sm ring-sea">
            <option value="">All</option>
            @foreach(['pending','paid','rejected','failed'] as $s)
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
                <th>User</th>
                <th>Amount</th>
                <th>MoMo</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $r)
                <tr class="align-top">
                    <td>{{ $r->id }}</td>
                    <td>
                        <span class="font-medium">{{ $r->user->name ?? '—' }}</span>
                        <p class="text-xs text-slate-500">{{ $r->user->email ?? '' }}</p>
                    </td>
                    <td>GH¢ {{ number_format((float) $r->amount, 2) }}</td>
                    <td class="text-sm">
                        {{ $r->momo_phone }}
                        <p class="text-xs text-slate-500">{{ $r->momo_account_name }}</p>
                    </td>
                    <td><span class="badge badge-neutral">{{ $r->status }}</span></td>
                    <td class="text-slate-500 text-sm whitespace-nowrap">{{ $r->created_at->format('M d, H:i') }}</td>
                    <td class="space-y-3 min-w-[200px]">
                        @if($r->status === 'pending')
                            <form method="POST" action="{{ route('admin.withdrawals.approve', $r) }}" class="space-y-2" data-confirm="Debit this user and send Paystack transfer to their MoMo?">
                                @csrf
                                <input type="text" name="admin_note" placeholder="Admin note (optional)" class="w-full text-xs ring-sea">
                                <button type="submit" class="btn btn-primary text-sm py-2 w-full">Approve &amp; pay</button>
                            </form>
                            <form method="POST" action="{{ route('admin.withdrawals.reject', $r) }}" class="space-y-2">
                                @csrf
                                <textarea name="rejection_reason" rows="2" required placeholder="Reason" class="w-full text-xs ring-sea"></textarea>
                                <button type="submit" class="btn btn-secondary text-sm py-2 w-full">Reject</button>
                            </form>
                        @else
                            <span class="text-xs text-slate-500">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-slate-500 text-center">No requests.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $requests->links() }}</div>
@endsection
