@extends('layouts.app')

@section('content')
<div class="max-w-lg">
    <h1 class="page-title">Create store for user</h1>
    <p class="text-slate-600 text-sm mb-6">Choose an existing account (not super admin). That user becomes the store owner and can edit the store from <strong>My stores</strong>.</p>
    <div class="card-elevated p-8">
        <form method="POST" action="{{ route('admin.stores.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div>
                <label for="owner_user_id" class="block text-sm font-medium text-slate-700 mb-1">Store owner <span class="text-red-600">*</span></label>
                <select name="owner_user_id" id="owner_user_id" required class="block w-full ring-sea">
                    <option value="">Select user…</option>
                    @foreach($eligibleOwners as $o)
                        <option value="{{ $o->id }}" {{ (string) old('owner_user_id') === (string) $o->id ? 'selected' : '' }}>{{ $o->name }} — {{ $o->email }}</option>
                    @endforeach
                </select>
                @error('owner_user_id')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Store name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="block w-full ring-sea">
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Short description</label>
                <textarea name="description" id="description" rows="3" class="block w-full ring-sea" placeholder="What they sell, delivery notes, etc.">{{ old('description') }}</textarea>
            </div>
            <div>
                <label for="whatsapp_phone" class="block text-sm font-medium text-slate-700 mb-1">WhatsApp phone <span class="text-red-600">*</span></label>
                <input type="text" name="whatsapp_phone" id="whatsapp_phone" value="{{ old('whatsapp_phone') }}" required class="block w-full ring-sea" placeholder="e.g. 23324… or 024…">
            </div>
            <div>
                <label for="contact_email" class="block text-sm font-medium text-slate-700 mb-1">Contact email (optional)</label>
                <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email') }}" class="block w-full ring-sea">
            </div>
            <div>
                <label for="location" class="block text-sm font-medium text-slate-700 mb-1">Location (optional)</label>
                <input type="text" name="location" id="location" value="{{ old('location') }}" class="block w-full ring-sea">
            </div>
            <div>
                <label for="logo" class="block text-sm font-medium text-slate-700 mb-1">Store logo (optional)</label>
                <input type="file" name="logo" id="logo" accept="image/jpeg,image/png,image/webp,image/gif" class="block w-full text-sm text-slate-600">
                <p class="text-xs text-slate-500 mt-1">Public shop &amp; search previews; defaults to WPS-SME logo if empty.</p>
                @error('logo')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-slate-300 text-[var(--sea)] focus:ring-[var(--sea)]">
                <label for="is_active" class="text-sm text-slate-700">Store is active</label>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary">Create store</button>
                <a href="{{ route('admin.stores.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
