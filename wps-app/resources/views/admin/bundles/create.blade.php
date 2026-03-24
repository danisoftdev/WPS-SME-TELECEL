@extends('layouts.app')

@section('content')
<h1 class="page-title">Create bundle</h1>
<div class="card-elevated p-6 max-w-md">
    <form method="POST" action="{{ route('admin.bundles.store') }}" class="space-y-5">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="block w-full ring-sea">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Network</label>
            <select name="network" required class="block w-full ring-sea">
                @foreach(['Telecel', 'MTN', 'Airtel Tigo'] as $n)
                    <option value="{{ $n }}" {{ old('network', 'Telecel') === $n ? 'selected' : '' }}>{{ $n }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Total data (GB)</label>
            <input type="number" name="total_data_gb" step="0.01" value="{{ old('total_data_gb') }}" required class="block w-full ring-sea">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Amount (GH¢)</label>
            <input type="number" name="amount" step="0.01" value="{{ old('amount') }}" required class="block w-full ring-sea">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Max beneficiaries</label>
            <input type="number" name="max_beneficiaries" min="1" value="{{ old('max_beneficiaries', 89) }}" required class="block w-full ring-sea">
        </div>
        <div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                <span class="text-sm text-slate-700">Active</span>
            </label>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>
@endsection
