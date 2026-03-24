@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-12 text-center">
    <h1 class="text-2xl font-semibold mb-4">Paystack wallet top-up</h1>
    <p class="mb-6">Amount: {{ $currency }} {{ number_format($topup->amount, 2) }}</p>

    <button id="paystack-button" class="btn btn-primary">
        Pay now
    </button>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
document.getElementById('paystack-button').addEventListener('click', function () {
    var handler = PaystackPop.setup({
        key: @json($publicKey),
        email: @json($topup->user->email),
        amount: {{ (int) round(((float) $topup->amount) * 100) }},
        currency: @json($currency),
        ref: @json($topup->reference),
        callback: function () {
            window.location.href = @json($callbackUrl);
        },
        onClose: function () {
            window.location.href = @json(route('wallet.show'));
        }
    });
    handler.openIframe();
});

// auto-start
document.getElementById('paystack-button').click();
</script>
@endsection

