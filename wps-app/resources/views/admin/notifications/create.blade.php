@extends('layouts.app')

@section('content')
<h1 class="page-title">New notification</h1>
<div class="card-elevated p-6 max-w-md">
    <form method="POST" action="{{ route('admin.notifications.store') }}" class="space-y-5">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Title</label>
            <input type="text" name="title" value="{{ old('title') }}" required class="block w-full ring-sea">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Message</label>
            <textarea name="message" rows="4" required class="block w-full ring-sea">{{ old('message') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Audience</label>
            <select name="audience" class="block w-full ring-sea">
                <option value="all" {{ old('audience') === 'all' ? 'selected' : '' }}>All</option>
                <option value="wholesalers">Wholesalers</option>
                <option value="retailers">Retailers</option>
                <option value="custom">Custom (by role)</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Active from (optional)</label>
            <input type="datetime-local" name="active_from" value="{{ old('active_from') }}" class="block w-full ring-sea">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Active until (optional)</label>
            <input type="datetime-local" name="active_until" value="{{ old('active_until') }}" class="block w-full ring-sea">
        </div>
        <div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                <span class="text-sm text-slate-700">Active</span>
            </label>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>
@endsection
