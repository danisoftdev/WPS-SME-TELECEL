@php $bundle = $row->bundleSubscription; @endphp
<div class="card-elevated p-6">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">{{ $bundle->name }}</h2>
            <p class="text-sm text-slate-500 mt-1">{{ $bundle->network ?? '—' }} · {{ $bundle->total_data_gb }} GB</p>
            <p class="text-xl font-bold text-[var(--sea-dark)] mt-2">GH¢ {{ number_format($row->selling_price, 2) }}</p>
        </div>
        <form method="POST" action="{{ route('shop.checkout.store', $store) }}" class="w-full sm:w-auto sm:min-w-[240px] space-y-3">
            @csrf
            <input type="hidden" name="bundle_subscription_id" value="{{ $bundle->id }}">
            <div>
                <label for="phone-{{ $bundle->id }}" class="block text-xs font-medium text-slate-600 mb-1">Your phone (for order / WhatsApp)</label>
                <input type="text" name="customer_phone" id="phone-{{ $bundle->id }}" value="{{ old('customer_phone') }}" required placeholder="e.g. 0240000000" class="block w-full ring-sea text-sm">
            </div>
            <button type="submit" class="btn btn-primary w-full">Pay with Paystack</button>
        </form>
    </div>
</div>
