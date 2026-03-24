@extends('layouts.app')

@section('content')
<h1 class="page-title">Edit notification</h1>
<div class="card-elevated p-6 max-w-md">
    <form method="POST" action="{{ route('admin.notifications.update', $notification) }}" class="space-y-5">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Title</label>
            <input type="text" name="title" value="{{ old('title', $notification->title) }}" required class="block w-full ring-sea">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Message</label>
            <textarea name="message" rows="4" required class="block w-full ring-sea">{{ old('message', $notification->message) }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Audience</label>
            <select name="audience" class="block w-full ring-sea">
                <option value="all" {{ old('audience', $notification->audience) === 'all' ? 'selected' : '' }}>All</option>
                <option value="wholesalers" {{ old('audience', $notification->audience) === 'wholesalers' ? 'selected' : '' }}>Wholesalers</option>
                <option value="retailers" {{ old('audience', $notification->audience) === 'retailers' ? 'selected' : '' }}>Retailers</option>
                <option value="custom">Custom</option>
            </select>
        </div>
        <div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $notification->is_active) ? 'checked' : '' }}>
                <span class="text-sm text-slate-700">Active</span>
            </label>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
