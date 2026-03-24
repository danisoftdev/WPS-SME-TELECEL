@extends('layouts.app')

@section('content')
<h1 class="page-title">Password reset requests</h1>

<form method="GET" class="mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
        <select name="status" class="block ring-sea">
            <option value="">All</option>
            @foreach(['pending','used','expired'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">Email</label>
        <input type="text" name="email" value="{{ request('email') }}" class="block ring-sea min-w-[180px]" placeholder="Search email">
    </div>
    <button type="submit" class="btn btn-secondary">Filter</button>
</form>

<div class="card overflow-hidden overflow-x-auto">
    <table class="min-w-full table-premium">
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Status</th>
                <th>Expires</th>
                <th>Created</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $r)
                <tr>
                    <td>{{ $r->id }}</td>
                    <td>{{ $r->email }}</td>
                    <td><span class="badge {{ $r->status === 'pending' ? 'badge-warning' : ($r->status === 'used' ? 'badge-success' : 'badge-neutral') }}">{{ $r->status }}</span></td>
                    <td class="text-slate-500 text-sm">{{ $r->expires_at?->format('M d, H:i') }}</td>
                    <td class="text-slate-500 text-sm">{{ $r->created_at?->format('M d, H:i') }}</td>
                    <td><a href="{{ route('admin.passwordResets.show', $r) }}" class="link-sea text-sm font-medium">View code</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-slate-500 text-center">No requests.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $items->links() }}</div>
@endsection

