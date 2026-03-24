@extends('layouts.app')

@section('content')
<h1 class="page-title">Password reset request #{{ $item->id }}</h1>

<div class="max-w-xl space-y-6">
    <div class="rounded-lg bg-white shadow border border-gray-200 p-5">
        <dl class="grid grid-cols-1 gap-2 text-sm">
            <dt class="text-gray-500">Email</dt><dd>{{ $item->email }}</dd>
            <dt class="text-gray-500">Status</dt><dd>{{ $item->status }}</dd>
            <dt class="text-gray-500">Expires</dt><dd>{{ $item->expires_at?->format('M d, Y H:i') }}</dd>
            <dt class="text-gray-500">Created</dt><dd>{{ $item->created_at?->format('M d, Y H:i') }}</dd>
        </dl>
    </div>

    <div class="rounded-lg bg-white shadow border border-gray-200 p-5">
        <h2 class="font-medium mb-2">Reset code</h2>
        @if($item->status !== 'pending')
            <p class="text-sm text-gray-600">This request is not pending. Code is shown for reference.</p>
        @endif
        <div class="mt-3 flex items-center gap-3">
            <span class="font-mono text-xl tracking-widest px-4 py-2 rounded bg-gray-100">{{ $code ?? 'Unavailable' }}</span>
            <button type="button" class="text-sm link-sea underline"
                    onclick="navigator.clipboard.writeText('{{ $code ?? '' }}')">
                Copy
            </button>
        </div>

        <div class="mt-4 flex gap-2">
            <form method="POST" action="{{ route('admin.passwordResets.used', $item) }}">
                @csrf
                <button type="submit" class="rounded bg-green-600 px-4 py-2 text-white">Mark used</button>
            </form>
            <form method="POST" action="{{ route('admin.passwordResets.expired', $item) }}">
                @csrf
                <button type="submit" class="rounded bg-gray-600 px-4 py-2 text-white">Mark expired</button>
            </form>
        </div>
    </div>

    <p><a href="{{ route('admin.passwordResets.index') }}" class="link-sea">← Back to reset requests</a></p>
</div>
@endsection

