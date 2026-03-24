@extends('layouts.app')

@section('content')
<h1 class="page-title">Users</h1>
<form method="GET" class="mb-6 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">Search</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email" class="block ring-sea min-w-[180px]">
    </div>
    <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">Role</label>
        <select name="role" class="block ring-sea">
        <option value="">All roles</option>
        @foreach($roles as $r)
            <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>{{ $r }}</option>
        @endforeach
    </select>
    </div>
    <button type="submit" class="btn btn-secondary">Filter</button>
</form>
<p class="mb-6"><a href="{{ route('admin.users.create') }}" class="btn btn-primary">Add user</a></p>
<div class="card overflow-hidden overflow-x-auto">
    <table class="min-w-full table-premium">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Roles</th>
                <th>Balance</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $u)
                <tr>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->email }}</td>
                    <td><span class="badge badge-neutral">{{ $u->roles->pluck('name')->join(', ') }}</span></td>
                    <td>GH¢ {{ number_format($u->wallet->balance ?? 0, 2) }}</td>
                    <td><span class="badge {{ $u->is_frozen ? 'badge-danger' : 'badge-success' }}">{{ $u->is_frozen ? 'Frozen' : 'Active' }}</span></td>
                    <td class="px-4 py-2">
                        @if(!$u->hasRole('Supplier'))
                            <a href="{{ route('admin.users.edit', $u) }}" class="link-sea text-sm font-semibold">Edit</a>
                            <a href="{{ route('admin.wallets.show', $u) }}" class="link-sea text-sm font-semibold ml-3">Wallet</a>
                            @canany(['credit_wallet','debit_wallet','view_wallet_logs'])
                                <form method="POST"
                                      action="{{ $u->is_frozen ? route('admin.wallets.unfreeze', $u) : route('admin.wallets.freeze', $u) }}"
                                      class="inline ml-2"
                                      data-confirm="{{ $u->is_frozen ? 'Unfreeze this account wallet?' : 'Freeze this account wallet?' }}">
                                    @csrf
                                    <button type="submit"
                                            class="{{ $u->is_frozen ? 'text-[rgb(11,127,130)]' : 'text-red-700' }} text-sm font-semibold ml-2">
                                        {{ $u->is_frozen ? 'Unfreeze' : 'Freeze' }}
                                    </button>
                                </form>
                            @endcanany
                            <form method="POST"
                                  action="{{ route('admin.users.destroy', $u) }}"
                                  class="inline ml-3"
                                  data-confirm="Delete this user account? This cannot be undone.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm font-semibold text-red-600">
                                    Del
                                </button>
                            </form>
                        @else
                            <span class="text-xs text-slate-500">Super Admin</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-6 text-slate-500">No users.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $users->links() }}</div>
@endsection
