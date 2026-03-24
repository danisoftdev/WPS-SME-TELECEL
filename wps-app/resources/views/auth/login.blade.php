@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto">
    <div class="form-shell">
        <h1 class="page-title mb-1">Login to WPS-SME</h1>
        <p class="section-sub mb-6">Sign in to your reseller or store account.</p>
        <form method="POST" action="{{ route('login') }}" class="form-stack">
            @csrf
            <div class="form-field">
                <label for="email" class="form-label">Email <span class="req">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                       class="block w-full ring-sea" autocomplete="username">
            </div>
            <div class="form-field">
                <label for="password" class="form-label">Password <span class="req">*</span></label>
                <input type="password" name="password" id="password" required class="block w-full ring-sea" autocomplete="current-password">
            </div>
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" id="remember">
                    <span class="text-sm text-slate-600">Remember me</span>
                </label>
                <a href="{{ route('password.request') }}" class="text-sm link-sea">Forgot password?</a>
            </div>
            <button type="submit" class="btn btn-primary w-full py-2.5">Login</button>
        </form>
        @if(($errors->has('email') || $errors->has('order') || $errors->any()) && !empty($account_unfreeze_petition_url))
            <div class="mt-5 rounded-xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800">
                <div class="font-medium">Having trouble logging in?</div>
                <div class="mt-1">If your account is frozen, you can request unfreeze here:</div>
                <a href="{{ $account_unfreeze_petition_url }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-block link-sea font-medium">
                    Request account unfreeze
                </a>
            </div>
        @endif
        <p class="mt-6 text-center text-sm text-slate-600">
            Don’t have an account? <a href="{{ route('register') }}" class="link-sea font-medium">Register</a>
        </p>
    </div>
</div>
@endsection
