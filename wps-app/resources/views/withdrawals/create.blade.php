@extends('layouts.app')

@section('content')
@php
    $wallet = $user->wallet;
    $balance = $wallet ? (float) $wallet->balance : 0;
@endphp
<div class="max-w-lg">
    <h1 class="page-title">Request withdrawal</h1>
    <p class="text-slate-600 text-sm mb-6">Available wallet balance: <strong>GH¢ {{ number_format($balance, 2) }}</strong>. Payouts go to <strong>{{ $user->momo_phone }}</strong> ({{ $user->momo_account_name }}). <a href="{{ route('store-owner.profile') }}" class="link-sea">Change</a></p>
    <div class="card-elevated p-8">
        <form method="POST" action="{{ route('withdrawals.store') }}" class="space-y-5">
            @csrf
            <div>
                <label for="amount" class="block text-sm font-medium text-slate-700 mb-1">Amount (GH¢)</label>
                <input type="number" step="0.01" min="1" name="amount" id="amount" value="{{ old('amount') }}" required class="block w-full ring-sea">
            </div>
            <p class="text-xs text-slate-500">You can have only one pending request at a time. An admin will approve and send funds to your MoMo.</p>
            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary">Send</button>
                <a href="{{ route('withdrawals.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
