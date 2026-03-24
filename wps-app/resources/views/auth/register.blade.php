@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto">
    <div class="card-elevated p-8">
        <h1 class="page-title mb-2">Register for WPS-SME</h1>
        @error('store_invite')
            <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900" role="alert">{{ $message }}</div>
        @enderror
        @if(!empty($inviteStore))
            <div class="mb-5 rounded-xl border border-[var(--sea-border)] bg-[var(--sea-muted)] p-4 text-sm text-slate-800">
                You are registering as a <strong>sub-agent</strong> for <strong>{{ $inviteStore->name }}</strong>. No account type is required — you will set resale prices after signup.
            </div>
        @endif
        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf
            @if(!empty($inviteStore) && !empty($storeInviteToken))
                <input type="hidden" name="store_invite" value="{{ $storeInviteToken }}">
            @endif
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Full name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus class="block w-full ring-sea">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required class="block w-full ring-sea">
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">Phone (optional)</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" placeholder="050*******" class="block w-full ring-sea">
            </div>
            @if(empty($inviteStore))
                <div class="rounded-xl border border-[var(--sea-border)] bg-[var(--sea-muted)] p-4 text-sm text-slate-800">
                    New accounts are <strong>Retailer / Agent</strong> by default. If you need a different account type (for example wholesaler), an administrator can update your role after you register. Supplier accounts are created only by the platform.
                </div>
            @endif
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input type="password" name="password" id="password" required minlength="8" class="block w-full ring-sea">
                <p class="mt-1 text-xs text-slate-500">At least 8 characters.</p>
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Confirm password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required class="block w-full ring-sea">
            </div>
            <button type="submit" class="btn btn-primary w-full py-2.5">Register</button>
        </form>
        <p class="mt-6 text-center text-sm text-slate-600">
            Already have an account? <a href="{{ route('login') }}" class="link-sea font-medium">Log in</a>
        </p>
    </div>
</div>
@endsection
