@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-12 text-center">
    <h1 class="text-2xl font-semibold mb-3">Wallet funded</h1>
    <p class="text-gray-600 mb-6">
        Top-up reference: <span class="font-mono">{{ $topup->reference }}</span>
    </p>
    <a href="{{ route('wallet.show') }}" class="btn btn-primary">
        Back to wallet
    </a>
</div>
@endsection

