@extends('layouts.app')

@section('content')
<h1 class="page-title">My stores</h1>

@if($wallet && $shopSalesStats)
    <div class="card-elevated p-6 mb-8 max-w-4xl border-[rgba(14,165,168,.2)]"
         x-data="{
            balance: {{ json_encode((float) $wallet->balance) }},
            balanceFormatted: @js(number_format((float) $wallet->balance, 2)),
            frozen: {{ $wallet->is_frozen ? 'true' : 'false' }},
            salesCount: {{ (int) $shopSalesStats['completed_count'] }},
            netTotal: {{ json_encode((float) $shopSalesStats['net_earnings_total']) }},
            netFormatted: @js(number_format((float) $shopSalesStats['net_earnings_total'], 2)),
            polling: false,
            lastSync: null,
            async refreshWallet() {
                if (this.polling) return;
                this.polling = true;
                try {
                    const r = await fetch('{{ route('stores.wallet-snapshot') }}', {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin'
                    });
                    if (!r.ok) return;
                    const d = await r.json();
                    this.balance = d.balance;
                    this.balanceFormatted = d.balance_formatted;
                    this.frozen = d.is_frozen;
                    this.salesCount = d.shop_sales_completed_count;
                    this.netTotal = d.shop_net_earnings_total;
                    this.netFormatted = d.shop_net_earnings_formatted;
                    this.lastSync = new Date();
                } catch (e) {}
                finally { this.polling = false; }
            }
         }"
         x-init="refreshWallet(); setInterval(() => refreshWallet(), 12000)">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-1">Store earnings wallet</p>
                <p class="text-3xl font-bold text-slate-900 tracking-tight">
                    GH¢ <span x-text="balanceFormatted">{{ number_format((float) $wallet->balance, 2) }}</span>
                </p>
                <p class="text-amber-600 text-sm font-medium mt-2" x-show="frozen" style="display: {{ $wallet->is_frozen ? 'block' : 'none' }}">Wallet is frozen. Contact admin.</p>
                <p class="text-slate-600 text-sm mt-3 max-w-xl leading-relaxed">
                    Customer payments on your shop use the <strong>platform Paystack</strong> account. When a payment is verified, your share (selling price minus platform fee) is <strong>credited here automatically</strong>—no manual step.
                </p>
                <p class="text-xs text-slate-500 mt-2 flex flex-wrap items-center gap-x-3 gap-y-1">
                    <span>Completed shop checkouts: <strong class="text-slate-700" x-text="salesCount">{{ $shopSalesStats['completed_count'] }}</strong></span>
                    <span class="hidden sm:inline text-slate-300">|</span>
                    <span>Net from shop sales: <strong class="text-slate-700">GH¢ <span x-text="netFormatted">{{ number_format((float) $shopSalesStats['net_earnings_total'], 2) }}</span></strong></span>
                </p>
                <p class="text-xs text-slate-400 mt-2" x-show="lastSync" x-cloak>
                    Last updated <span x-text="lastSync ? lastSync.toLocaleTimeString() : ''"></span> · refreshes every 12s
                </p>
            </div>
            <div class="flex flex-col sm:flex-row lg:flex-col gap-2 shrink-0">
                @can('place_orders')
                    <a href="{{ route('wallet.show') }}" class="btn btn-primary text-center justify-center">Full wallet &amp; history</a>
                    <a href="{{ route('withdrawals.index') }}" class="btn btn-soft text-center justify-center">Withdrawals</a>
                @else
                    <p class="text-sm text-slate-500 max-w-xs">Your role may not include the reseller wallet page. Balance still updates when customers pay.</p>
                @endcan
            </div>
        </div>
    </div>
@endif

<p class="mb-6"><a href="{{ route('stores.create') }}" class="btn btn-primary">Create store</a></p>
<div class="card overflow-hidden overflow-x-auto">
    <table class="min-w-full table-premium">
        <thead>
            <tr>
                <th>Name</th>
                <th>Shop URL</th>
                <th>Sub-agents</th>
                <th>Active</th>
                <th>Invite link</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($stores as $s)
                <tr>
                    <td class="font-medium">{{ $s->name }}</td>
                    <td class="text-sm">
                        <a href="{{ route('shop.show', $s) }}" target="_blank" rel="noopener" class="link-sea">/shop/{{ $s->slug }}</a>
                        <p class="mt-1"><a href="{{ route('stores.pricing.edit', $s) }}" class="text-xs text-slate-600 hover:text-[var(--sea)]">Pricing</a></p>
                    </td>
                    <td>{{ $s->sub_agents_count }}</td>
                    <td>{{ $s->is_active ? 'Yes' : 'No' }}</td>
                    <td>
                        <input type="text" readonly value="{{ url('/join-store/'.$s->invite_token) }}" class="text-xs w-full max-w-xs ring-sea" onclick="this.select()">
                    </td>
                    <td class="whitespace-nowrap">
                        <a href="{{ route('stores.edit', $s) }}" class="link-sea text-sm font-medium">Edit</a>
                        <form method="POST" action="{{ route('stores.destroy', $s) }}" class="inline ml-2" data-confirm="Delete this store?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 text-sm font-medium hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-slate-500 text-center">No stores yet. Create one for your public shop and sub-agents.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $stores->links() }}</div>
@endsection
