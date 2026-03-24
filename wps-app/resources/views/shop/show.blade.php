@extends('layouts.app')

@section('content')
@php
    $waDigits = $store->whatsapp_phone ? preg_replace('/\D+/', '', $store->whatsapp_phone) : '';
@endphp
<div class="max-w-3xl mx-auto">
    <div class="card-elevated p-8 mb-8">
        <div class="flex flex-col sm:flex-row sm:items-start gap-5">
            @if($store->logoDisplayUrl())
                <img src="{{ $store->logoDisplayUrl() }}" alt="{{ $store->name }}" width="96" height="96" class="h-20 w-20 sm:h-24 sm:w-24 rounded-xl object-cover ring-1 ring-slate-200/90 shadow-sm shrink-0">
            @else
                <img src="{{ \App\Support\Branding::appLogoUrl() }}" alt="{{ $store->name }}" width="96" height="96" class="h-20 w-20 sm:h-24 sm:w-24 rounded-xl object-contain ring-1 ring-slate-200/90 shadow-sm shrink-0 bg-white p-1">
            @endif
            <div class="min-w-0 flex-1">
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $store->name }}</h1>
        @if($store->description)
            <p class="mt-3 text-slate-600 whitespace-pre-line">{{ $store->description }}</p>
        @endif
        <div class="mt-5 flex flex-wrap gap-4 text-sm text-slate-700">
            @if($waDigits !== '')
                <a href="https://wa.me/{{ $waDigits }}" target="_blank" rel="noopener" class="link-sea font-medium">WhatsApp store</a>
            @endif
            @if($store->contact_email)
                <a href="mailto:{{ $store->contact_email }}" class="link-sea font-medium">{{ $store->contact_email }}</a>
            @endif
            @if($store->location)
                <span class="text-slate-600">{{ $store->location }}</span>
            @endif
        </div>
            </div>
        </div>
    </div>

    @if($lines->isEmpty())
        <p class="text-slate-500 text-center py-12">This store has no products listed yet.</p>
    @elseif($networksInShop->count() > 1)
        <div x-data="{ filter: 'all' }" class="space-y-8">
            <div class="card-elevated p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="min-w-0">
                    <label for="shop-network-filter" class="text-sm font-medium text-slate-800">Network</label>
                    <p class="text-xs text-slate-500 mt-0.5">Choose a network or view all Telecel, MTN, and Airtel Tigo bundles below.</p>
                </div>
                <select id="shop-network-filter" x-model="filter" class="block w-full sm:max-w-xs ring-sea text-sm shrink-0">
                    <option value="all">All networks</option>
                    @foreach($networksInShop as $networkName)
                        <option value="{{ \Illuminate\Support\Str::slug($networkName) }}">{{ $networkName }}</option>
                    @endforeach
                </select>
            </div>

            @foreach($networksInShop as $networkName)
                @php $networkSlug = \Illuminate\Support\Str::slug($networkName); @endphp
                <section
                    x-show="filter === 'all' || filter === '{{ $networkSlug }}'"
                    class="scroll-mt-24 space-y-6"
                    aria-labelledby="shop-heading-{{ $networkSlug }}"
                >
                    <h2 id="shop-heading-{{ $networkSlug }}" class="text-lg font-semibold text-slate-800 pb-2 border-b border-slate-200">{{ $networkName }}</h2>
                    <div class="space-y-6">
                        @foreach($linesByNetwork->get($networkName) as $row)
                            @include('shop.partials.bundle_line', ['store' => $store, 'row' => $row])
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @else
        <div class="space-y-6">
            @foreach($lines as $row)
                @include('shop.partials.bundle_line', ['store' => $store, 'row' => $row])
            @endforeach
        </div>
    @endif
</div>
@endsection
