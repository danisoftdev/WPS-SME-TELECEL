@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-12 text-center">
    <h1 class="text-2xl font-semibold mb-2">Checkout</h1>
    <p class="text-slate-600 text-sm mb-1">{{ $checkout->store->name }}</p>
    <p class="mb-6">Amount: {{ $currency }} {{ number_format((float) $checkout->amount_total, 2) }}</p>

    <button id="paystack-button" type="button" class="btn btn-primary">
        Pay now
    </button>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
document.getElementById('paystack-button').addEventListener('click', function () {
    var handler = PaystackPop.setup({
        key: @json($publicKey),
        email: @json($payEmail),
        amount: {{ (int) round(((float) $checkout->amount_total) * 100) }},
        currency: @json($currency),
        ref: @json($checkout->reference),
        callback: function () {
            window.location.href = @json($callbackUrl);
        },
        onClose: function () {
            window.location.href = @json(route('shop.show', $checkout->store));
        }
    });
    handler.openIframe();
});

document.getElementById('paystack-button').click();
</script>
@endsection
