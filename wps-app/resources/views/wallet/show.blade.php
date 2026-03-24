@extends('layouts.app')

@section('content')
<h1 class="page-title">My Wallet</h1>
<p class="text-sm text-slate-600 max-w-2xl mb-6 leading-relaxed">
    <strong>Shop sales:</strong> When buyers pay on your public store via Paystack, your net amount (after any platform fee) is credited here as soon as the payment is verified. Look for transactions labeled <em>Shop checkout …</em> in the history below.
</p>
<div class="stat-card max-w-md mb-8">
    <p class="stat-label">Balance</p>
    <p class="stat-value mt-0.5">GH¢ {{ number_format($wallet->balance, 2) }}</p>
    @if($wallet->is_frozen)
        <p class="text-amber-600 text-sm font-medium mt-1">Wallet is frozen. Contact admin.</p>
    @endif
</div>

<div class="card p-6 mb-8 max-w-md">
    <h2 class="section-title">Fund wallet</h2>
    <form method="POST" action="{{ route('wallet.topups.store') }}" class="flex gap-3 items-end">
        @csrf
        <div class="flex-1">
            <label class="block text-sm font-medium text-slate-700 mb-1">Amount (GH¢)</label>
            <input type="number" name="amount" step="0.01" min="1" required class="block w-full ring-sea">
        </div>
        <button type="submit" class="btn btn-primary py-2.5">Add funds</button>
    </form>
    @if($errors->has('wallet'))
        <p class="mt-2 text-sm text-red-600">{{ $errors->first('wallet') }}</p>
    @endif
</div>

<h2 class="section-title">Transaction history</h2>
<div class="card overflow-hidden">
    <table class="min-w-full table-premium">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Balance after</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $t)
                <tr>
                    <td class="text-sm text-slate-500">{{ $t->created_at->format('M d, H:i') }}</td>
                    <td><span class="badge {{ $t->type === 'credit' || $t->type === 'refund' ? 'badge-success' : 'badge-danger' }}">{{ $t->type }}</span></td>
                    <td>{{ $t->type === 'debit' ? '-' : '+' }} GH¢ {{ number_format($t->amount, 2) }}</td>
                    <td>GH¢ {{ number_format($t->balance_after, 2) }}</td>
                    <td class="text-sm">{{ $t->description }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-slate-500 text-center">No transactions yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $transactions->links() }}</div>
@endsection
