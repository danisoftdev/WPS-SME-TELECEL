@extends('layouts.app')

@section('content')
<h1 class="page-title">Withdrawal requests</h1>
<p class="mb-6 flex flex-wrap gap-3 items-center">
    <a href="{{ route('withdrawals.create') }}" class="btn btn-primary">New request</a>
    <a href="{{ route('store-owner.profile') }}" class="btn btn-soft text-sm">Store owner profile / MoMo</a>
</p>
<div class="card overflow-hidden overflow-x-auto">
    <table class="min-w-full table-premium">
        <thead>
            <tr>
                <th>Date</th>
                <th>Amount</th>
                <th>Status</th>
                <th>MoMo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $r)
                <tr>
                    <td class="text-sm text-slate-600">{{ $r->created_at->format('M d, Y H:i') }}</td>
                    <td class="font-medium">GH¢ {{ number_format((float) $r->amount, 2) }}</td>
                    <td>
                        <span class="badge badge-neutral">{{ $r->status }}</span>
                        @if($r->status === 'rejected' && $r->rejection_reason)
                            <p class="text-xs text-slate-500 mt-1 max-w-xs">{{ $r->rejection_reason }}</p>
                        @endif
                    </td>
                    <td class="text-sm text-slate-600">{{ $r->momo_phone }}<br><span class="text-xs">{{ $r->momo_account_name }}</span></td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-8 text-slate-500 text-center">No requests yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $requests->links() }}</div>
@endsection
