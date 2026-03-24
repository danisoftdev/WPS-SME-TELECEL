@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto" x-data="{ show: {{ $show_support_popup ? 'true' : 'false' }} }">
    <div class="card-elevated p-8">
        <h1 class="page-title mb-2">Reset password</h1>
        <p class="text-sm text-slate-600 mb-6">
            Enter the code you received from admin, then choose a new password.
        </p>

        <template x-if="show && '{{ $support_url ?? '' }}'.length">
            <div class="mb-6 rounded-xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800">
                <div class="font-medium">Action required</div>
                <div class="mt-1">A code request has been sent to admin. Please chat admin to receive the code:</div>
                <a href="{{ $support_url ?? '#' }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-block link-sea font-medium">Chat admin for the code</a>
                <button type="button" class="ml-3 text-xs text-slate-600 underline" @click="show=false">Dismiss</button>
            </div>
        </template>

        <form method="POST" action="{{ route('password.reset.submit') }}" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $email_prefill) }}" required class="block w-full ring-sea">
            </div>
            <div>
                <label for="code" class="block text-sm font-medium text-slate-700 mb-1">Code</label>
                <input type="text" name="code" id="code" value="{{ old('code') }}" required placeholder="6-digit code" class="block w-full ring-sea">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">New password</label>
                <input type="password" name="password" id="password" required minlength="8" class="block w-full ring-sea">
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Confirm new password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required class="block w-full ring-sea">
            </div>
            <button type="submit" class="btn btn-primary w-full py-2.5">Set new password</button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-600">
            <a href="{{ route('login') }}" class="link-sea font-medium">Back to login</a>
        </p>
    </div>
</div>
@endsection
