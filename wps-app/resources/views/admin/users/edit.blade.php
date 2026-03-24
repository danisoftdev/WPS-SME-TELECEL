@extends('layouts.app')

@section('content')
<h1 class="page-title">Edit user</h1>
<form method="POST" action="{{ route('admin.users.update', $user) }}" class="max-w-md space-y-4">
    @csrf
    @method('PUT')
    <div>
        <label class="block text-sm font-medium text-gray-700">Name</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">New password (leave blank to keep)</label>
        <input type="password" name="password" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Confirm password</label>
        <input type="password" name="password_confirmation" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Daily order limit</label>
        <input type="number" name="daily_order_limit" value="{{ old('daily_order_limit', $user->daily_order_limit) }}" min="1" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2">
    </div>
    <div>
        <label class="flex items-center gap-2"><input type="checkbox" name="is_frozen" value="1" {{ old('is_frozen', $user->is_frozen) ? 'checked' : '' }}> Account frozen</label>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Roles</label>
        @foreach($roles as $role)
            <label class="flex items-center gap-2 mt-1">
                <input type="checkbox" name="roles[]" value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'checked' : '' }}>
                {{ $role->name }}
            </label>
        @endforeach
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
</form>
@endsection
