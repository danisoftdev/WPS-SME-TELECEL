@extends('layouts.app')

@section('content')
<h1 class="page-title">Order #{{ $order->id }}</h1>
<div class="rounded-lg bg-white shadow p-6 max-w-lg">
    <dl class="grid grid-cols-1 gap-2">
        <dt class="text-sm text-gray-500">Phone</dt><dd>{{ $order->phone_number }}</dd>
        <dt class="text-sm text-gray-500">Plan</dt><dd>{{ $order->resellerPlan->data_size_gb ?? '-' }} GB — GH¢ {{ number_format($order->amount, 2) }} debited</dd>
        @if($order->store)
            <dt class="text-sm text-gray-500">Store</dt><dd>{{ $order->store->name }}</dd>
        @endif
        <dt class="text-sm text-gray-500">Status</dt><dd><span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100">{{ $order->status }}</span></dd>
        <dt class="text-sm text-gray-500">Placed</dt><dd>{{ $order->created_at->format('M d, Y H:i') }}</dd>
    </dl>
    <h2 class="mt-6 font-medium">Status history</h2>
    <ul class="mt-2 space-y-1 text-sm">
        @foreach($order->statusHistory as $h)
            <li>{{ $h->from_status ?? '—' }} → {{ $h->to_status }} ({{ $h->created_at->format('M d H:i') }}) @if($h->notes) — {{ $h->notes }} @endif</li>
        @endforeach
    </ul>
</div>
<p class="mt-4"><a href="{{ route('orders.index') }}" class="link-sea">← Back to orders</a></p>
@endsection
