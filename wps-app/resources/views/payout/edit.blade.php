@extends('layouts.app')

@section('content')
<div class="max-w-lg">
    <h1 class="page-title">Payout profile (MoMo)</h1>
    <p class="text-slate-600 text-sm mb-6">Use the same mobile money number and registered name you use with your network. Admins send approved withdrawals to this MoMo via Paystack.</p>
    <div class="card-elevated p-8">
        <form method="POST" action="{{ route('payout.update') }}" class="space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label for="momo_phone" class="block text-sm font-medium text-slate-700 mb-1">MoMo phone</label>
                <input type="text" name="momo_phone" id="momo_phone" value="{{ old('momo_phone', $user->momo_phone) }}" required class="block w-full ring-sea" placeholder="e.g. 0240000000">
            </div>
            <div>
                <label for="momo_account_name" class="block text-sm font-medium text-slate-700 mb-1">Account name (as on MoMo)</label>
                <input type="text" name="momo_account_name" id="momo_account_name" value="{{ old('momo_account_name', $user->momo_account_name) }}" required class="block w-full ring-sea" placeholder="Full name">
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>
@endsection
