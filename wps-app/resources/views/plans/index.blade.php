@extends('layouts.app')

@section('content')
<h1 class="page-title">My data plans</h1>
<p class="mb-6"><a href="{{ route('plans.create') }}" class="btn btn-primary">Add plan</a></p>
<div class="card overflow-hidden overflow-x-auto">
    <table class="min-w-full table-premium">
        <thead>
            <tr>
                <th>Subscription</th>
                <th>Data size</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Active</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($plans as $p)
                <tr>
                    <td>{{ $p->userSubscription->bundleSubscription->name ?? '-' }}</td>
                    <td>{{ $p->data_size_gb }} GB</td>
                    <td>GH¢ {{ number_format($p->price, 2) }}</td>
                    <td>{{ $p->available_units }}</td>
                    <td>{{ $p->is_active ? 'Yes' : 'No' }}</td>
                    <td>
                        <a href="{{ route('plans.edit', $p) }}" class="link-sea text-sm font-medium">Edit</a>
                        <form method="POST" action="{{ route('plans.destroy', $p) }}" class="inline ml-2" data-confirm="Delete this plan?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 text-sm font-medium hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-slate-500 text-center">No plans. <a href="{{ route('subscriptions.index') }}" class="link-sea">Add a subscription</a> first, then create plans.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $plans->links() }}</div>
@endsection
