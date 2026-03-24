@extends('layouts.app')

@section('content')
<div class="max-w-2xl">
    <h1 class="page-title">Store owner profile</h1>
    <p class="page-lead">Manage <strong class="text-slate-700">MoMo payout</strong> for wallet withdrawals. Only store owners see this page; super admin accounts do not use MoMo here.</p>

    <div class="form-shell">
        <div class="form-shell-header">
            <div class="form-shell-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
                <h2 class="section-title">Your stores</h2>
                <p class="section-sub">Public shop, settings, and bundle pricing for each store you own.</p>
            </div>
        </div>
        <div class="surface-inset">
            @foreach($stores as $s)
                <div class="list-row">
                    <span class="list-row-title">{{ $s->name }}</span>
                    <span class="flex flex-wrap gap-2">
                        <a href="{{ route('shop.show', $s) }}" class="btn-ghost" target="_blank" rel="noopener">Shop</a>
                        <a href="{{ route('stores.edit', $s) }}" class="btn-ghost">Edit</a>
                        <a href="{{ route('stores.pricing.edit', $s) }}" class="btn-ghost">Pricing</a>
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="form-shell">
        <div class="form-shell-header">
            <div class="form-shell-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <h2 class="section-title">MoMo payout</h2>
                <p class="section-sub">Use the number registered on your mobile money wallet. Approved withdrawals are sent to this number.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('store-owner.profile.update') }}" class="form-stack">
            @csrf
            @method('PUT')
            <div class="form-field">
                <label for="momo_phone" class="form-label">MoMo phone <span class="req">*</span></label>
                <input type="tel" name="momo_phone" id="momo_phone" value="{{ old('momo_phone', $user->momo_phone) }}" required class="block w-full ring-sea" placeholder="e.g. 23324…" autocomplete="tel">
                <p class="form-hint">Include country code if you use international format.</p>
            </div>
            <div class="form-field">
                <label for="momo_account_name" class="form-label">Account name <span class="req">*</span></label>
                <input type="text" name="momo_account_name" id="momo_account_name" value="{{ old('momo_account_name', $user->momo_account_name) }}" required class="block w-full ring-sea" placeholder="As shown on MoMo" autocomplete="name">
                <p class="form-hint">Must match the name on the wallet receiving payouts.</p>
            </div>
            <div class="pt-1">
                <button type="submit" class="btn btn-primary px-8">Save payout details</button>
            </div>
        </form>
    </div>
</div>
@endsection
