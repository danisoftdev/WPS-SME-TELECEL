@extends('layouts.app')

@section('content')
<div class="max-w-lg">
    <h1 class="page-title">Create store</h1>
    <div class="card-elevated p-8">
        <form method="POST" action="{{ route('stores.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Store name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="block w-full ring-sea">
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Short description</label>
                <textarea name="description" id="description" rows="3" class="block w-full ring-sea" placeholder="What you sell, delivery notes, etc.">{{ old('description') }}</textarea>
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
                <p class="text-xs text-slate-500 mt-1">Shown on your public shop, browser tab, and link previews. Max 2&nbsp;MB. If omitted, the WPS-SME logo is used.</p>
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
                <button type="submit" class="btn btn-primary">Create</button>
                <a href="{{ route('stores.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
