@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto">
    <div class="card-elevated p-8">
        <h1 class="page-title mb-2">Forgot password</h1>
        <p class="text-sm text-slate-600 mb-6">
            Submit your email. A reset code will be generated and sent to the admin. You will then chat admin to receive the code and create a new password.
        </p>

        <form method="POST" action="{{ route('password.request.submit') }}" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus class="block w-full ring-sea">
            </div>
            <button type="submit" class="btn btn-primary w-full py-2.5">Send request</button>
        </form>

        @if(!empty($support_url))
            <div class="mt-6 rounded-xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800">
                <div class="font-medium">Need help?</div>
                <div class="mt-1">Chat admin here:</div>
                <a href="{{ $support_url }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-block link-sea font-medium">Open chat/support link</a>
            </div>
        @endif

        <p class="mt-6 text-center text-sm text-slate-600">
            <a href="{{ route('login') }}" class="link-sea font-medium">Back to login</a>
        </p>
    </div>
</div>
@endsection
