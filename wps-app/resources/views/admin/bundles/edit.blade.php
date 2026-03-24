@extends('layouts.app')

@section('content')
<h1 class="page-title">Edit bundle</h1>
<div class="card-elevated p-6 max-w-md">
    <form method="POST" action="{{ route('admin.bundles.update', $bundle) }}" class="space-y-5">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
            <input type="text" name="name" value="{{ old('name', $bundle->name) }}" required class="block w-full ring-sea">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Network</label>
            <select name="network" required class="block w-full ring-sea">
                @foreach(['Telecel', 'MTN', 'Airtel Tigo'] as $n)
                    <option value="{{ $n }}" {{ old('network', $bundle->network ?? 'Telecel') === $n ? 'selected' : '' }}>{{ $n }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Total data (GB)</label>
            <input type="number" name="total_data_gb" step="0.01" value="{{ old('total_data_gb', $bundle->total_data_gb) }}" required class="block w-full ring-sea">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Amount (GH¢)</label>
            <input type="number" name="amount" step="0.01" value="{{ old('amount', $bundle->amount) }}" required class="block w-full ring-sea">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Max beneficiaries</label>
            <input type="number" name="max_beneficiaries" min="1" value="{{ old('max_beneficiaries', $bundle->max_beneficiaries) }}" required class="block w-full ring-sea">
        </div>
        <div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $bundle->is_active) ? 'checked' : '' }}>
                <span class="text-sm text-slate-700">Active</span>
            </label>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
