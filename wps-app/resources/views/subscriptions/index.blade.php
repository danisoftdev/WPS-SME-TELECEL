@extends('layouts.app')

@section('content')
<h1 class="page-title">My subscriptions</h1>
<p class="mb-6"><a href="{{ route('subscriptions.create') }}" class="btn btn-primary">Add subscription</a></p>
<div class="card overflow-hidden overflow-x-auto">
    <table class="min-w-full table-premium">
        <thead>
            <tr>
                <th>Bundle</th>
                <th>Network</th>
                <th>Balance (GB)</th>
                <th>Beneficiaries</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($subscriptions as $s)
                <tr>
                    <td>{{ $s->bundleSubscription->name ?? '-' }}</td>
                    <td><span class="badge badge-neutral">{{ $s->bundleSubscription->network ?? 'Telecel' }}</span></td>
                    <td>{{ number_format($s->balance_gb, 2) }}</td>
                    <td>{{ is_array($s->beneficiaries) ? count($s->beneficiaries) : 0 }}</td>
                    <td><a href="{{ route('subscriptions.edit', $s) }}" class="link-sea text-sm font-medium">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-slate-500 text-center">No subscriptions yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $subscriptions->links() }}</div>
@endsection
