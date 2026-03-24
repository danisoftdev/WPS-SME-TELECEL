@extends('layouts.app')

@section('content')
<div class="max-w-lg">
    <h1 class="page-title">Edit store</h1>
    <div class="card-elevated p-8 space-y-6">
        <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-4 space-y-3">
            <div>
                <p class="text-sm font-medium text-slate-700 mb-1">Customer shop (public)</p>
                <input type="text" readonly value="{{ $store->customerShopUrl() }}" class="text-sm w-full ring-sea" onclick="this.select()">
                <p class="text-xs text-slate-500 mt-2">Buyers pay here via Paystack; your wallet is credited after fees.</p>
                <p class="mt-2"><a href="{{ route('stores.pricing.edit', $store) }}" class="link-sea text-sm font-medium">Set shop catalog prices</a></p>
            </div>
            <div class="pt-3 border-t border-slate-200">
                <p class="text-sm font-medium text-slate-700 mb-1">Sub-agent invite link</p>
                <input type="text" readonly value="{{ url('/join-store/'.$store->invite_token) }}" class="text-sm w-full ring-sea" onclick="this.select()">
                <p class="text-xs text-slate-500 mt-2">Share this URL so sub-agents can register or join while logged in.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('stores.update', $store) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Store name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $store->name) }}" required class="block w-full ring-sea">
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Short description</label>
                <textarea name="description" id="description" rows="3" class="block w-full ring-sea">{{ old('description', $store->description) }}</textarea>
            </div>
            <div>
                <label for="whatsapp_phone" class="block text-sm font-medium text-slate-700 mb-1">WhatsApp phone <span class="text-red-600">*</span></label>
                <input type="text" name="whatsapp_phone" id="whatsapp_phone" value="{{ old('whatsapp_phone', $store->whatsapp_phone) }}" required class="block w-full ring-sea">
            </div>
            <div>
                <label for="contact_email" class="block text-sm font-medium text-slate-700 mb-1">Contact email (optional)</label>
                <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $store->contact_email) }}" class="block w-full ring-sea">
            </div>
            <div>
                <label for="location" class="block text-sm font-medium text-slate-700 mb-1">Location (optional)</label>
                <input type="text" name="location" id="location" value="{{ old('location', $store->location) }}" class="block w-full ring-sea">
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-4 space-y-3">
                <p class="text-sm font-medium text-slate-700">Store logo (optional)</p>
                @if($store->logoDisplayUrl())
                    <div class="flex items-center gap-4">
                        <img src="{{ $store->logoDisplayUrl() }}" alt="" class="h-16 w-16 rounded-lg object-cover ring-1 ring-slate-200">
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="remove_logo" value="1" {{ old('remove_logo') ? 'checked' : '' }} class="rounded border-slate-300 text-[var(--sea)] focus:ring-[var(--sea)]">
                            Remove current logo (use app default on shop)
                        </label>
                    </div>
                @endif
                <input type="file" name="logo" id="logo" accept="image/jpeg,image/png,image/webp,image/gif" class="block w-full text-sm text-slate-600">
                <p class="text-xs text-slate-500">Used on your public shop and for search / social previews. Max 2&nbsp;MB.</p>
                @error('logo')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $store->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-[var(--sea)] focus:ring-[var(--sea)]">
                <label for="is_active" class="text-sm text-slate-700">Store is active</label>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('stores.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </form>
    </div>
</div>
@endsection
