@extends('layouts.app')

@section('content')
<div class="max-w-lg">
    <h1 class="page-title">Add subscription</h1>
    <p class="page-lead">Pick a network, then choose a bundle and set your remaining data balance (GB).</p>

    <div class="form-shell">
        <form method="GET" action="{{ route('subscriptions.create') }}" class="form-field max-w-md">
            <label for="sub-network" class="form-label">Network</label>
            <select name="network" id="sub-network" class="block w-full ring-sea" onchange="this.form.submit()">
                @foreach($networks as $n)
                    <option value="{{ $n }}" {{ $selectedNetwork === $n ? 'selected' : '' }}>{{ $n }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @if($bundles->isEmpty())
        <div class="form-shell">
            <p class="text-slate-600 text-sm">No bundles available for this network. Contact admin.</p>
        </div>
    @else
    <div class="form-shell">
        <div class="form-shell-header">
            <div class="form-shell-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div>
                <h2 class="section-title">Bundle &amp; balance</h2>
                <p class="section-sub">Active bundles for {{ $selectedNetwork }}.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('subscriptions.store') }}" class="form-stack max-w-md">
            @csrf
            <div class="form-field">
                <label for="bundle_subscription_id" class="form-label">Bundle <span class="req">*</span></label>
                <select name="bundle_subscription_id" id="bundle_subscription_id" required class="block w-full ring-sea">
                    @foreach($bundles as $b)
                        <option value="{{ $b->id }}">{{ $b->name }} — {{ $b->total_data_gb }} GB, GH¢ {{ number_format($b->amount, 2) }}, max {{ $b->max_beneficiaries }} beneficiaries</option>
                    @endforeach
                </select>
            </div>
            <div class="form-field">
                <label for="balance_gb" class="form-label">Balance (GB) <span class="req">*</span></label>
                <input type="number" name="balance_gb" id="balance_gb" step="0.01" min="0" value="{{ old('balance_gb') }}" required class="block w-full ring-sea" placeholder="0.00">
                <p class="form-hint">Remaining data from your pool for this bundle.</p>
            </div>
            <p class="text-sm text-slate-500 -mt-1">Beneficiaries can be added when you edit this subscription.</p>
            <button type="submit" class="btn btn-primary">Add subscription</button>
        </form>
    </div>
    @endif
</div>
@endsection
