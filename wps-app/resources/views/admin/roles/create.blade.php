@extends('layouts.app')

@section('content')
<h1 class="page-title">New role</h1>
<form method="POST" action="{{ route('admin.roles.store') }}" class="max-w-md space-y-4">
    @csrf
    <div>
        <label class="block text-sm font-medium text-gray-700">Name</label>
        <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Permissions</label>
        @foreach($permissions as $group => $perms)
            <div class="mt-2">
                <span class="text-xs font-medium text-gray-500 uppercase">{{ $group }}</span>
                @foreach($perms as $p)
                    <label class="flex items-center gap-2 mt-1">
                        <input type="checkbox" name="permissions[]" value="{{ $p->name }}" {{ in_array($p->name, old('permissions', [])) ? 'checked' : '' }}>
                        {{ $p->name }}
                    </label>
                @endforeach
            </div>
        @endforeach
    </div>
    <button type="submit" class="btn btn-primary">Create</button>
</form>
@endsection
