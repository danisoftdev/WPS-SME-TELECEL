@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-12 text-center card-elevated p-8">
    <span class="badge badge-success mb-4">Paid</span>
    <h1 class="text-2xl font-semibold text-slate-900 mb-2">Thank you</h1>
    <p class="text-slate-600 text-sm mb-6">Your payment was successful. The merchant has been credited.</p>
    <a href="{{ route('shop.show', $checkout->store) }}" class="btn btn-primary">Back to store</a>
</div>
@endsection
