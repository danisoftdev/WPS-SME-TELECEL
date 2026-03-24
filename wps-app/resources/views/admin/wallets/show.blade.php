@extends('layouts.app')

@section('content')
<h1 class="page-title">Wallet — {{ $wallet->user->name }}</h1>
<div class="max-w-2xl space-y-6">
    <div class="rounded-lg bg-white shadow p-6">
        <p class="text-sm text-gray-500">Balance</p>
        <p class="text-2xl font-semibold">GH¢ {{ number_format($wallet->balance, 2) }}</p>
        <p class="text-sm {{ $wallet->is_frozen ? 'text-amber-600' : 'text-gray-500' }}">{{ $wallet->is_frozen ? 'Frozen' : 'Active' }}</p>
    </div>
    @if(auth()->user()->can('credit_wallet'))
    <div class="rounded-lg bg-white shadow p-6">
        <h2 class="font-medium mb-4">Credit</h2>
        <form method="POST" action="{{ route('admin.wallets.credit', $wallet->user) }}" class="flex gap-2 flex-wrap">
            @csrf
            <input type="number" name="amount" step="0.01" min="0.01" placeholder="Amount" required class="rounded border border-gray-300 px-3 py-2">
            <input type="text" name="description" placeholder="Description" class="rounded border border-gray-300 px-3 py-2">
            <button type="submit" class="rounded bg-green-600 px-4 py-2 text-white">Credit</button>
        </form>
    </div>
    @endif
    @if(auth()->user()->can('debit_wallet'))
    <div class="rounded-lg bg-white shadow p-6">
        <h2 class="font-medium mb-4">Debit</h2>
        <form method="POST" action="{{ route('admin.wallets.debit', $wallet->user) }}" class="flex gap-2 flex-wrap">
            @csrf
            <input type="number" name="amount" step="0.01" min="0.01" placeholder="Amount" required class="rounded border border-gray-300 px-3 py-2">
            <input type="text" name="description" placeholder="Description" class="rounded border border-gray-300 px-3 py-2">
            <button type="submit" class="rounded bg-red-600 px-4 py-2 text-white">Debit</button>
        </form>
    </div>
    @endif
    <div class="rounded-lg bg-white shadow p-6">
        <h2 class="font-medium mb-4">{{ $wallet->is_frozen ? 'Unfreeze' : 'Freeze' }} wallet</h2>
        <form method="POST" action="{{ $wallet->is_frozen ? route('admin.wallets.unfreeze', $wallet->user) : route('admin.wallets.freeze', $wallet->user) }}">
            @csrf
            <button type="submit" class="rounded {{ $wallet->is_frozen ? 'bg-green-600' : 'bg-amber-600' }} px-4 py-2 text-white">{{ $wallet->is_frozen ? 'Unfreeze' : 'Freeze' }}</button>
        </form>
    </div>
    <div class="rounded-lg bg-white shadow p-6">
        <h2 class="font-medium mb-4">Transaction log</h2>
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50"><tr><th class="px-4 py-2 text-left">Date</th><th class="px-4 py-2 text-left">Type</th><th class="px-4 py-2 text-left">Amount</th><th class="px-4 py-2 text-left">Balance after</th><th class="px-4 py-2 text-left">Description</th></tr></thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($transactions as $t)
                    <tr>
                        <td class="px-4 py-2">{{ $t->created_at->format('M d H:i') }}</td>
                        <td class="px-4 py-2">{{ $t->type }}</td>
                        <td class="px-4 py-2">{{ $t->type === 'debit' ? '-' : '+' }} GH¢ {{ number_format($t->amount, 2) }}</td>
                        <td class="px-4 py-2">GH¢ {{ number_format($t->balance_after, 2) }}</td>
                        <td class="px-4 py-2">{{ $t->description }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">{{ $transactions->links() }}</div>
    </div>
</div>
<p class="mt-6"><a href="{{ route('admin.wallets.index') }}" class="link-sea">← Back to wallets</a></p>
@endsection
