@extends('layouts.app')

@section('content')
<h1 class="page-title">Wallets</h1>
<form method="GET" class="mb-6 flex gap-3 items-end">
    <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">Search</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="User name or email" class="block ring-sea min-w-[200px]">
    </div>
    <button type="submit" class="btn btn-secondary">Search</button>
</form>
<div class="card overflow-hidden overflow-x-auto">
    <table class="min-w-full table-premium">
        <thead>
            <tr>
                <th>User</th>
                <th>Balance</th>
                <th>Frozen</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $u)
                <tr>
                    <td>{{ $u->name }} ({{ $u->email }})</td>
                    <td>GH¢ {{ number_format($u->wallet->balance ?? 0, 2) }}</td>
                    <td>{{ ($u->wallet->is_frozen ?? false) ? 'Yes' : 'No' }}</td>
                    <td><a href="{{ route('admin.wallets.show', $u) }}" class="link-sea text-sm font-medium">View / Credit / Debit</a></td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-8 text-slate-500 text-center">No wallets.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $users->links() }}</div>
@endsection
