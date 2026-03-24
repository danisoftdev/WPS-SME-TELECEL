@extends('layouts.app')

@section('content')
<h1 class="page-title">Store resale prices</h1>
<p class="text-slate-600 text-sm mb-6">Set your selling price for each bundle. It must be <strong>strictly greater</strong> than the platform base price. Leave blank to remove a price.</p>
@if(auth()->user()->store)
    <p class="text-sm text-slate-700 mb-4">Store: <span class="font-semibold">{{ auth()->user()->store->name }}</span></p>
@endif

<form method="POST" action="{{ route('sub-agent.pricing.update') }}">
    @csrf
    @method('PUT')
    <div class="card overflow-hidden overflow-x-auto">
        <table class="min-w-full table-premium">
            <thead>
                <tr>
                    <th>Bundle</th>
                    <th>Network</th>
                    <th>Base (platform)</th>
                    <th>Your selling price</th>
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
        <div class="mt-6">
            <button type="submit" class="btn btn-primary">Save prices</button>
        </div>
    @endif
</form>
@endsection
