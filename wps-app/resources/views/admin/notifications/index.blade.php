@extends('layouts.app')

@section('content')
<h1 class="page-title">Broadcast notifications</h1>
<p class="mb-6"><a href="{{ route('admin.notifications.create') }}" class="btn btn-primary">New notification</a></p>
<div class="card overflow-hidden">
    <table class="min-w-full table-premium">
        <thead>
            <tr>
                <th>Title</th>
                <th>Audience</th>
                <th>Active</th>
                <th>Created</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($notifications as $n)
                <tr>
                    <td>{{ $n->title }}</td>
                    <td><span class="badge badge-neutral">{{ $n->audience }}</span></td>
                    <td>{{ $n->is_active ? 'Yes' : 'No' }}</td>
                    <td class="text-slate-500 text-sm">{{ $n->created_at->format('M d, H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.notifications.edit', $n) }}" class="link-sea text-sm font-medium">Edit</a>
                        <form method="POST" action="{{ route('admin.notifications.destroy', $n) }}" class="inline ml-2" data-confirm="Delete this notification?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 text-sm font-medium hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-slate-500 text-center">No notifications.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $notifications->links() }}</div>
@endsection
