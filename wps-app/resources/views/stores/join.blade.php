@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="card-elevated p-8">
        <h1 class="page-title mb-2">Join {{ $store->name }}</h1>
        <p class="text-slate-600 text-sm mb-6">You are invited to become a sub-agent under this store. Your resale prices must stay <strong>above</strong> the platform base price.</p>
        @guest
            <p class="mb-4"><a href="{{ route('register', ['store_invite' => $token]) }}" class="btn btn-primary">Create an account</a></p>
            <p class="text-sm text-slate-600">Already have an account? <a href="{{ route('login') }}" class="link-sea font-medium">Log in</a>, then return here to accept.</p>
        @else
            @if(auth()->user()->ownedStores()->exists())
                <p class="text-amber-800 text-sm">Store owners cannot join as sub-agents.</p>
            @elseif(auth()->user()->store_id && (int) auth()->user()->store_id !== (int) $store->id)
                <p class="text-amber-800 text-sm">Your account is already linked to another store.</p>
            @elseif((int) auth()->user()->store_id === (int) $store->id)
                <p class="text-slate-600 text-sm">You are already a member of this store.</p>
                <p class="mt-4"><a href="{{ route('sub-agent.pricing.index') }}" class="btn btn-soft">Set store prices</a></p>
            @else
                <form method="POST" action="{{ route('stores.join.accept', ['token' => $token]) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Join this store</button>
                </form>
            @endif
        @endguest
    </div>
</div>
@endsection
