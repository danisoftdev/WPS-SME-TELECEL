@extends('layouts.app')

@section('content')
<h1 class="page-title">API tokens</h1>
<p class="text-slate-600 text-sm mb-6 max-w-2xl">Create long-lived tokens for integrations (same permissions as your account). Use <code class="text-xs bg-slate-100 px-1 rounded">Authorization: Bearer &lt;token&gt;</code> on <code class="text-xs bg-slate-100 px-1 rounded">/api/v1/*</code>. Revoke any token you no longer trust.</p>

@if(session('api_token_plain'))
    <div class="mb-6 card-elevated p-5 border-[var(--sea-border)] bg-[var(--sea-muted)] max-w-2xl">
        <p class="text-sm font-semibold text-slate-900 mb-2">Copy this token now — it will not be shown again.</p>
        <p class="text-xs text-slate-600 mb-2">{{ session('api_token_name') }}</p>
        <input type="text" readonly value="{{ session('api_token_plain') }}" class="w-full text-sm font-mono ring-sea" onclick="this.select()">
    </div>
@endif

<div class="card-elevated p-6 max-w-md mb-8">
    <h2 class="section-title">New token</h2>
    <form method="POST" action="{{ route('api-tokens.store') }}" class="space-y-4">
        @csrf
        <div>
            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Label</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="e.g. My POS server" class="block w-full ring-sea">
        </div>
        <button type="submit" class="btn btn-primary">Create token</button>
    </form>
</div>

<div class="card overflow-hidden overflow-x-auto max-w-3xl">
    <table class="min-w-full table-premium">
        <thead>
            <tr>
                <th>Name</th>
                <th>Created</th>
                <th>Last used</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($tokens as $t)
                <tr>
                    <td class="font-medium">{{ $t->name }}</td>
                    <td class="text-sm text-slate-600">{{ $t->created_at->format('M d, Y H:i') }}</td>
                    <td class="text-sm text-slate-600">{{ $t->last_used_at ? $t->last_used_at->format('M d, Y H:i') : '—' }}</td>
                    <td>
                        <form method="POST" action="{{ route('api-tokens.destroy', $t->id) }}" class="inline" data-confirm="Revoke this token? Integrations using it will stop working.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 text-sm font-medium hover:underline">Revoke</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-8 text-slate-500 text-center">No tokens yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
