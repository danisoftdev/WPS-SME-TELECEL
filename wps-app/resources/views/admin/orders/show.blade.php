@extends('layouts.app')

@section('content')
<h1 class="page-title">Order #{{ $order->id }}</h1>
<div class="max-w-2xl space-y-6">
    <div class="rounded-lg bg-white shadow p-6">
        <dl class="grid grid-cols-1 gap-2">
            <dt class="text-sm text-gray-500">User</dt><dd>{{ $order->user->name }} ({{ $order->user->email }})</dd>
            <dt class="text-sm text-gray-500">Phone</dt><dd>{{ $order->phone_number }}</dd>
            <dt class="text-sm text-gray-500">Plan</dt><dd>{{ $order->resellerPlan->data_size_gb ?? '-' }} GB — debited GH¢ {{ number_format($order->amount, 2) }}</dd>
            @if($order->store_id)
                <dt class="text-sm text-gray-500">Store</dt><dd>{{ $order->store?->name ?? ('#'.$order->store_id) }}</dd>
            @endif
            @if($order->sub_agent_user_id)
                <dt class="text-sm text-gray-500">Sub-agent</dt><dd>{{ $order->subAgent?->name ?? '—' }} ({{ $order->subAgent?->email }})</dd>
            @endif
            @if($order->igate_submitted_at || $order->igate_last_error || $order->igate_last_http_code)
                <dt class="text-sm text-gray-500">iGate</dt>
                <dd class="text-sm">
                    @if($order->igate_submitted_at)
                        <span class="text-green-700">Submitted {{ $order->igate_submitted_at->format('M d, Y H:i') }}</span>
                        @if($order->igate_last_http_code)
                            <span class="text-gray-500">(HTTP {{ $order->igate_last_http_code }})</span>
                        @endif
                    @elseif($order->igate_last_error)
                        <span class="text-red-700">Last error: {{ \Illuminate\Support\Str::limit($order->igate_last_error, 200) }}</span>
                        @if($order->igate_last_http_code)
                            <span class="text-gray-500">(HTTP {{ $order->igate_last_http_code }})</span>
                        @endif
                    @endif
                </dd>
            @endif
            <dt class="text-sm text-gray-500">Status</dt><dd><span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100">{{ $order->status }}</span></dd>
            <dt class="text-sm text-gray-500">Placed</dt><dd>{{ $order->created_at->format('M d, Y H:i') }}</dd>
        </dl>
    </div>
    @if(auth()->user()->can('update_order_status') && !$order->isFinal())
    <div class="rounded-lg bg-white shadow p-6">
        <h2 class="font-medium mb-4">Update status</h2>
        <form method="POST" action="{{ route('admin.orders.updateStatus', $order) }}" class="space-y-2">
            @csrf
            @method('PATCH')
            <select name="status" class="rounded border border-gray-300 px-3 py-2 w-full" required>
                @if($order->status === 'pending')
                    <option value="processing">Processing</option>
                @endif
                @if($order->status === 'processing')
                    <option value="sent">Sent</option>
                    <option value="failed">Failed</option>
                @endif
                @if($order->status === 'failed')
                    <option value="refunded">Refunded</option>
                @endif
            </select>
            <textarea name="notes" rows="2" placeholder="Notes (optional)" class="rounded border border-gray-300 px-3 py-2 w-full"></textarea>
            @if($order->status === 'processing')
            <label class="flex items-center gap-2"><input type="checkbox" name="refund_on_failed" value="1"> Refund wallet if marking as Failed</label>
            @endif
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
    @endif
    <div class="rounded-lg bg-white shadow p-6">
        <h2 class="font-medium mb-4">Internal notes</h2>
        <form method="POST" action="{{ route('admin.orders.updateNotes', $order) }}">
            @csrf
            @method('PUT')
            <textarea name="internal_notes" rows="3" class="rounded border border-gray-300 px-3 py-2 w-full">{{ $order->internal_notes }}</textarea>
            <button type="submit" class="mt-2 rounded bg-gray-600 px-4 py-2 text-white">Save notes</button>
        </form>
    </div>
    <div class="rounded-lg bg-white shadow p-6">
        <h2 class="font-medium mb-4">Status history</h2>
        <ul class="space-y-1 text-sm">
            @foreach($order->statusHistory as $h)
                <li>{{ $h->from_status ?? '—' }} → {{ $h->to_status }} ({{ $h->created_at->format('M d H:i') }}) @if($h->notes) — {{ $h->notes }} @endif</li>
            @endforeach
        </ul>
    </div>
</div>
<p class="mt-6"><a href="{{ route('admin.orders.index') }}" class="link-sea">← Back to orders</a></p>
@endsection
