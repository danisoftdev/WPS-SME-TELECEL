@extends('layouts.app')

@section('content')
<h1 class="page-title">Roles</h1>
<p class="mb-6"><a href="{{ route('admin.roles.create') }}" class="btn btn-primary">New role</a></p>
<div class="card overflow-hidden">
    <table class="min-w-full table-premium">
        <thead>
            <tr>
                <th>Name</th>
                <th>Permissions count</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($roles as $r)
                <tr>
                    <td>{{ $r->name }}</td>
                    <td>{{ $r->permissions_count }}</td>
                    <td><a href="{{ route('admin.roles.edit', $r) }}" class="link-sea text-sm font-medium">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="3" class="px-4 py-8 text-slate-500 text-center">No roles.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
