@extends('layouts.app')

@section('content')
<h1 class="page-title">New data plan</h1>
<form method="GET" action="{{ route('plans.create') }}" class="max-w-md mb-4 flex gap-2">
    <div class="flex-1">
        <label class="block text-sm font-medium text-gray-700 mb-1">Network</label>
        <select name="network" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2" onchange="this.form.submit()">
            @foreach($networks as $n)
                <option value="{{ $n }}" {{ $selectedNetwork === $n ? 'selected' : '' }}>{{ $n }}</option>
            @endforeach
        </select>
    </div>
</form>
@if($subscriptions->isEmpty())
    <p class="text-gray-600">You have no subscriptions for <span class="font-semibold">{{ $selectedNetwork }}</span>. <a href="{{ route('subscriptions.create', ['network' => $selectedNetwork]) }}" class="text-indigo-600">Add subscription</a>.</p>
@else
<form method="POST" action="{{ route('plans.store') }}" class="max-w-md space-y-4">
    @csrf
    <div>
        <label class="block text-sm font-medium text-gray-700">From subscription</label>
        <select name="user_subscription_id" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2">
            @foreach($subscriptions as $s)
                <option value="{{ $s->id }}">{{ $s->bundleSubscription->name ?? 'Bundle' }} ({{ $s->balance_gb }} GB left)</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Data size (GB)</label>
        <input type="number" name="data_size_gb" step="0.01" min="0.01" value="{{ old('data_size_gb') }}" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Selling price (GH¢)</label>
        <input type="number" name="price" step="0.01" min="0" value="{{ old('price') }}" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Available units (stock)</label>
        <input type="number" name="available_units" min="0" value="{{ old('available_units', 0) }}" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2">
    </div>
    <button type="submit" class="btn btn-primary">Create</button>
</form>
@endif
@endsection
