@extends('layouts.app')

@section('content')
<h1 class="page-title">Edit plan ({{ $plan->data_size_gb }} GB)</h1>
<form method="POST" action="{{ route('plans.update', $plan) }}" class="max-w-md space-y-4">
    @csrf
    @method('PUT')
    <div>
        <label class="block text-sm font-medium text-gray-700">Selling price (GH¢)</label>
        <input type="number" name="price" step="0.01" min="0" value="{{ old('price', $plan->price) }}" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Available units (stock)</label>
        <input type="number" name="available_units" min="0" value="{{ old('available_units', $plan->available_units) }}" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2">
    </div>
    <div>
        <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }}> Active</label>
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
</form>
@endsection
