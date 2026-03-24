@extends('layouts.app')

@section('content')
<h1 class="page-title">Bundle Subscriptions</h1>
<p class="mb-6"><a href="{{ route('admin.bundles.create') }}" class="btn btn-primary">Add bundle</a></p>
<div class="card overflow-hidden overflow-x-auto">
    <table class="min-w-full table-premium">
        <thead>
            <tr>
                <th>Name</th>
                <th>Network</th>
                <th>Data (GB)</th>
                <th>Amount (GH¢)</th>
                <th>Max beneficiaries</th>
                <th>Active</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($bundles as $b)
                <tr>
                    <td>{{ $b->name }}</td>
                    <td><span class="badge badge-neutral">{{ $b->network ?? 'Telecel' }}</span></td>
                    <td>{{ $b->total_data_gb }}</td>
                    <td>{{ number_format($b->amount, 2) }}</td>
                    <td>{{ $b->max_beneficiaries }}</td>
                    <td>{{ $b->is_active ? 'Yes' : 'No' }}</td>
                    <td>
                        <a href="{{ route('admin.bundles.edit', $b) }}" class="link-sea text-sm font-medium">Edit</a>
                        <form method="POST" action="{{ route('admin.bundles.destroy', $b) }}" class="inline ml-2" data-confirm="Delete this bundle?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 text-sm font-medium hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-slate-500 text-center">No bundles.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $bundles->links() }}</div>
@endsection
