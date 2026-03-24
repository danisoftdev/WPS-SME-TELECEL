@extends('layouts.app')

@section('content')
<h1 class="page-title">Store catalog prices</h1>
<p class="text-slate-600 text-sm mb-2">Store: <span class="font-semibold text-slate-800">{{ $store->name }}</span></p>
<p class="text-slate-600 text-sm mb-6">Set the price customers pay on your public shop. Each price must be <strong>strictly greater</strong> than the platform base. Leave blank to remove a product from the shop.</p>
<p class="mb-6"><a href="{{ route('shop.show', $store) }}" target="_blank" rel="noopener" class="link-sea text-sm font-medium">Open customer shop →</a></p>

<form method="POST" action="{{ route('stores.pricing.update', $store) }}">
    @csrf
    @method('PUT')
    <div class="card overflow-hidden overflow-x-auto">
        <table class="min-w-full table-premium">
            <thead>
                <tr>
                    <th>Bundle</th>
                    <th>Network</th>
                    <th>Base (platform)</th>
                    <th>Your shop price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bundles as $b)
                    @php $row = $prices->get($b->id); @endphp
                    <tr>
                        <td class="font-medium">{{ $b->name }}</td>
                        <td>{{ $b->network ?? '—' }}</td>
                        <td>GH¢ {{ number_format($b->amount, 2) }}</td>
                        <td>
                            <input type="number" step="0.01" min="0" name="prices[{{ $b->id }}]" value="{{ old('prices.'.$b->id, $row?->selling_price) }}" placeholder="—" class="w-36 ring-sea">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($bundles->isEmpty())
        <p class="text-slate-500 mt-4">No active bundles on the platform yet.</p>
    @else
        <div class="mt-6 flex gap-3">
            <button type="submit" class="btn btn-primary">Save prices</button>
            <a href="{{ route('stores.index') }}" class="btn btn-secondary">Back to stores</a>
        </div>
    @endif
</form>
@endsection
