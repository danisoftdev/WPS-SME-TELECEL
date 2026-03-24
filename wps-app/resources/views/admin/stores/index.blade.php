@extends('layouts.app')

@section('content')
<h1 class="page-title">Stores for users</h1>
<p class="text-slate-600 text-sm mb-4 max-w-2xl">Create and assign stores to existing user accounts. Super admin accounts cannot own a store. Store owners manage MoMo payout details in their <strong>Store owner profile</strong> after they have at least one store.</p>
<form method="GET" action="{{ route('admin.stores.index') }}" class="mb-6 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-[200px] max-w-md">
        <label for="store-search-q" class="block text-xs font-medium text-slate-500 mb-1">Search</label>
        <input type="search" name="q" id="store-search-q" value="{{ $q }}" placeholder="Store name, slug, owner name or email" class="block w-full ring-sea" autocomplete="off">
    </div>
    <button type="submit" class="btn btn-secondary">Search</button>
    @if($q !== '')
        <a href="{{ route('admin.stores.index') }}" class="btn btn-soft text-sm">Clear</a>
    @endif
</form>
<p class="mb-6"><a href="{{ route('admin.stores.create') }}" class="btn btn-primary">Create store for user</a></p>
<div class="card overflow-hidden overflow-x-auto">
    <table class="min-w-full table-premium">
        <thead>
            <tr>
                <th>Store</th>
                <th>Owner</th>
                <th>Shop</th>
                <th>Sub-agents</th>
                <th>Active</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($stores as $s)
                <tr>
                    <td class="font-medium">{{ $s->name }}</td>
                    <td>
                        <span class="text-slate-800">{{ $s->owner?->name ?? '—' }}</span>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $s->owner?->email }}</p>
                    </td>
                    <td class="text-sm">
                        <a href="{{ route('shop.show', $s) }}" target="_blank" rel="noopener" class="link-sea">/shop/{{ $s->slug }}</a>
                    </td>
                    <td>{{ $s->sub_agents_count ?? 0 }}</td>
                    <td><span class="badge {{ $s->is_active ? 'badge-success' : 'badge-neutral' }}">{{ $s->is_active ? 'Yes' : 'No' }}</span></td>
                    <td class="whitespace-nowrap">
                        <a href="{{ route('admin.stores.edit', $s) }}" class="link-sea text-sm font-medium">Edit</a>
                        <form method="POST" action="{{ route('admin.stores.destroy', $s) }}" class="inline ml-3" data-confirm="Delete this store? Sub-agents must be removed first.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 text-sm font-medium hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-slate-500 text-center">No stores yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $stores->links() }}</div>
@endsection
