@extends('layouts.app')

@section('content')
<div class="max-w-lg">
    <h1 class="page-title">Edit subscription</h1>
    <p class="page-lead">{{ $subscription->bundleSubscription->name ?? 'Bundle' }}</p>

    <div class="form-shell">
        <form method="POST" action="{{ route('subscriptions.update', $subscription) }}" class="form-stack max-w-md">
            @csrf
            @method('PUT')
            <div class="form-field">
                <label for="balance_gb" class="form-label">Balance (GB) <span class="req">*</span></label>
                <input type="number" name="balance_gb" id="balance_gb" step="0.01" min="0" value="{{ old('balance_gb', $subscription->balance_gb) }}" required class="block w-full ring-sea">
            </div>
            <p class="form-hint">Beneficiaries are stored as structured data; extend this form if you need to edit phone numbers in the UI.</p>
            <button type="submit" class="btn btn-primary">Update subscription</button>
        </form>
    </div>
</div>
@endsection
