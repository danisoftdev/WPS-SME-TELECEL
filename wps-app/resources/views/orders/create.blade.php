@extends('layouts.app')

@section('content')
<h1 class="page-title">Place Data Order</h1>

<form method="GET" action="{{ route('orders.create') }}" class="max-w-md mb-6 flex gap-3">
    <div class="flex-1">
        <label class="block text-sm font-medium text-slate-700 mb-1">Network</label>
        <select name="network" class="block w-full ring-sea" onchange="this.form.submit()">
            @foreach($networks as $n)
                <option value="{{ $n }}" {{ $selectedNetwork === $n ? 'selected' : '' }}>{{ $n }}</option>
            @endforeach
        </select>
    </div>
</form>

@if($plans->isEmpty())
    <div class="card p-6 max-w-md">
        <p class="text-slate-600">You have no active data plans for <span class="font-semibold text-slate-900">{{ $selectedNetwork }}</span>. <a href="{{ route('plans.index') }}" class="link-sea">Create a plan</a> first, or <a href="{{ route('subscriptions.index') }}" class="link-sea">add a subscription</a>.</p>
    </div>
@else
@if(auth()->user()->hasRole('SubAgent'))
    @php
        $missingStorePrice = $plans->filter(function ($p) use ($subAgentPriceMap) {
            $bid = $p->userSubscription?->bundle_subscription_id;
            return ! $bid || ! isset($subAgentPriceMap[$bid]);
        })->count();
    @endphp
    @if($missingStorePrice > 0)
        <div class="card p-4 mb-4 max-w-md border-amber-200 bg-amber-50/80 text-sm text-amber-900">
            Some plans need a <strong>Store price</strong> before you can place an order. <a href="{{ route('sub-agent.pricing.index') }}" class="link-sea font-medium">Configure store prices</a>.
        </div>
    @endif
@endif
<div class="card-elevated p-6 max-w-md">
    <form method="POST" action="{{ route('orders.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="network" value="{{ $selectedNetwork }}">
        <div>
            <label for="phone_number" class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
            <input type="text" name="phone_number" id="phone_number" placeholder="050*******" value="{{ old('phone_number', request('phone_number')) }}" required class="block w-full ring-sea">
        </div>
        <div>
            <label for="reseller_plan_id" class="block text-sm font-medium text-slate-700 mb-1">Data plan</label>
            <select name="reseller_plan_id" id="reseller_plan_id" required class="block w-full ring-sea">
                @foreach($plans as $p)
                    @php
                        $bundleId = $p->userSubscription?->bundle_subscription_id;
                        $hasStorePrice = $bundleId && isset($subAgentPriceMap[$bundleId]);
                        $charge = $hasStorePrice ? $subAgentPriceMap[$bundleId] : (float) $p->price;
                    @endphp
                    <option value="{{ $p->id }}" {{ old('reseller_plan_id', request('reseller_plan_id')) == $p->id ? 'selected' : '' }}>
                        {{ $p->data_size_gb }} GB — GH¢ {{ number_format($charge, 2) }}
                        @if(auth()->user()->hasRole('SubAgent'))
                            @if($hasStorePrice) (your store price) @else — add under Store prices @endif
                        @endif
                        ({{ $p->available_units }} left)
                    </option>
                @endforeach
            </select>
        </div>
        <div class="rounded-xl bg-amber-50 border border-amber-200 p-4">
            <p class="text-sm text-amber-800"><strong>Notice:</strong> No refund after order is marked SENT. Wallet is debited when you submit.</p>
            <label class="mt-3 flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="confirmation_checked" value="1" required>
                <span class="text-sm text-amber-800">I confirm and accept the above</span>
            </label>
        </div>
        <button type="submit" class="btn btn-primary w-full py-2.5">Place Order</button>
    </form>
</div>
@endif
@endsection
