@extends('layouts.app')

@section('content')
<h1 class="page-title">System settings</h1>
<div class="max-w-5xl mx-auto">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
    <!-- Settings list -->
    @if(auth()->user()->hasRole('Supplier'))
        <div class="group rounded-2xl bg-white shadow-sm border border-slate-200 p-5 flex items-center justify-between gap-4 hover:border-[rgba(14,165,168,.35)] hover:shadow transition">
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <div class="h-10 w-10 rounded-2xl bg-[rgba(14,165,168,.12)] text-[rgb(14,165,168)] flex items-center justify-center font-semibold">A</div>
                    <div class="min-w-0">
                        <h2 class="text-sm font-semibold text-slate-900">Account settings</h2>
                        <p class="text-xs text-slate-500">Your profile and password</p>
                    </div>
                </div>
                <p class="text-xs text-slate-700 mt-2 truncate">
                    <span class="font-medium">{{ auth()->user()->name }}</span> — {{ auth()->user()->email }}
                </p>
            </div>
            <button type="button" class="btn btn-primary js-open-modal" data-modal="modal-account">Edit</button>
        </div>
    @endif

    <div class="group rounded-2xl bg-white shadow-sm border border-slate-200 p-5 flex items-center justify-between gap-4 hover:border-[rgba(14,165,168,.35)] hover:shadow transition">
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <div class="h-10 w-10 rounded-2xl bg-[rgba(14,165,168,.12)] text-[rgb(14,165,168)] flex items-center justify-center font-semibold">O</div>
                <div class="min-w-0">
                    <h2 class="text-sm font-semibold text-slate-900">Order protections</h2>
                    <p class="text-xs text-slate-500">Global limits and protections</p>
                </div>
            </div>
            <p class="text-xs text-slate-700 mt-2">
                Max pending orders: <span class="font-medium">{{ $max_pending_orders_per_user }}</span>
            </p>
        </div>
        <button type="button" class="btn btn-primary js-open-modal" data-modal="modal-limits">Edit</button>
    </div>

    <div class="group rounded-2xl bg-white shadow-sm border border-slate-200 p-5 flex items-center justify-between gap-4 hover:border-[rgba(14,165,168,.35)] hover:shadow transition">
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <div class="h-10 w-10 rounded-2xl bg-[rgba(14,165,168,.12)] text-[rgb(14,165,168)] flex items-center justify-center font-semibold">P</div>
                <div class="min-w-0">
                    <h2 class="text-sm font-semibold text-slate-900">Paystack</h2>
                    <p class="text-xs text-slate-500">Keys &amp; API options (all payments use this)</p>
                </div>
            </div>
            <div class="mt-2 text-xs text-slate-700 space-y-1">
                <div class="truncate"><span class="font-medium">Public:</span> {{ $paystack_public_masked ?? 'Not set' }}</div>
                <div class="truncate"><span class="font-medium">Secret:</span> {{ $paystack_secret_masked ?? 'Not set' }}</div>
                <div class="truncate"><span class="font-medium">API:</span> {{ $paystack_effective_base_url ?? '—' }} · {{ $paystack_effective_currency ?? 'GHS' }}</div>
            </div>
        </div>
        <button type="button" class="btn btn-primary js-open-modal" data-modal="modal-paystack">Edit</button>
    </div>

    <div class="group rounded-2xl bg-white shadow-sm border border-slate-200 p-5 flex items-center justify-between gap-4 hover:border-[rgba(14,165,168,.35)] hover:shadow transition">
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <div class="h-10 w-10 rounded-2xl bg-[rgba(14,165,168,.12)] text-[rgb(14,165,168)] flex items-center justify-center font-semibold">S</div>
                <div class="min-w-0">
                    <h2 class="text-sm font-semibold text-slate-900">Support link</h2>
                    <p class="text-xs text-slate-500">Shown when account is frozen</p>
                </div>
            </div>
            <p class="text-xs text-slate-700 mt-2 truncate">
                {{ $account_unfreeze_petition_url ? $account_unfreeze_petition_url : 'Not set' }}
            </p>
        </div>
        <button type="button" class="btn btn-primary js-open-modal" data-modal="modal-unfreeze">Edit</button>
    </div>
    </div>
</div>

<!-- Backdrop -->
<div id="settings-backdrop" class="hidden fixed inset-0 bg-black/40 z-40"></div>

<!-- Modals -->
@if(auth()->user()->hasRole('Supplier'))
<div id="modal-account" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
    <div class="w-full max-w-3xl rounded-2xl bg-white shadow-xl border border-slate-200 p-6 text-slate-900">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-lg font-semibold">Account settings</h3>
                <p class="text-xs text-gray-500 mt-1">Update your profile and password.</p>
            </div>
            <button type="button" class="text-slate-500 hover:text-slate-700 js-close-modal">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-5 grid gap-4 md:grid-cols-2">
            @csrf
            @method('PUT')
            <input type="hidden" name="section" value="account">
            <div>
                <label class="block text-sm font-medium text-slate-700">Name</label>
                <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[rgba(14,165,168,.25)]">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Email</label>
                <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[rgba(14,165,168,.25)]">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', auth()->user()->phone) }}" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[rgba(14,165,168,.25)]">
            </div>
            <div class="md:col-span-2 border-t border-slate-200 pt-4">
                <h4 class="text-sm font-medium text-slate-700">Change password (optional)</h4>
                <div class="grid gap-4 md:grid-cols-3 mt-3">
                    <div>
                        <label class="block text-sm text-slate-600">Current password</label>
                        <input type="password" name="current_password" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[rgba(14,165,168,.25)]">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-600">New password</label>
                        <input type="password" name="new_password" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[rgba(14,165,168,.25)]">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-600">Confirm new password</label>
                        <input type="password" name="new_password_confirmation" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[rgba(14,165,168,.25)]">
                    </div>
                </div>
            </div>
            <div class="md:col-span-2 flex justify-end gap-2">
                <button type="button" class="btn btn-soft js-close-modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@endif

<div id="modal-limits" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
    <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl border border-slate-200 p-6 text-slate-900">
        <div class="flex items-start justify-between">
            <h3 class="text-lg font-semibold text-slate-900">Order protections</h3>
            <button type="button" class="text-slate-500 hover:text-slate-700 js-close-modal">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-5 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="section" value="limits">
            <div>
                <label class="block text-sm font-medium text-slate-700">Max pending orders per user</label>
                <input type="number" name="max_pending_orders_per_user" value="{{ old('max_pending_orders_per_user', $max_pending_orders_per_user) }}" min="1" max="100" required class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[rgba(14,165,168,.25)]">
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" class="btn btn-soft js-close-modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-paystack" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
    <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl border border-slate-200 p-6 text-slate-900">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Paystack</h3>
                <p class="text-xs text-slate-500 mt-1">Same keys as in your Paystack dashboard. Encrypted in the database. Wallet top-ups, guest shop checkout, and withdrawal transfers all use this configuration.</p>
            </div>
            <button type="button" class="text-slate-500 hover:text-slate-700 js-close-modal">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-5 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="section" value="paystack">
            <div class="text-xs text-slate-600">
                <div><span class="font-medium">Current public:</span> {{ $paystack_public_masked ?? 'Not set' }}</div>
                <div class="mt-1"><span class="font-medium">Current secret:</span> {{ $paystack_secret_masked ?? 'Not set' }}</div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Paystack public key</label>
                <input type="text" name="paystack_public_key" value="" placeholder="pk_live_..." class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[rgba(14,165,168,.25)]">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Paystack secret key</label>
                <input type="password" name="paystack_secret_key" value="" placeholder="sk_live_..." class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[rgba(14,165,168,.25)]">
            </div>
            <div class="border-t border-slate-200 pt-4 space-y-3">
                <p class="text-xs text-slate-600">Optional: clear a field and save to use the value from <code class="text-xs bg-slate-100 px-1 rounded">.env</code> / defaults instead.</p>
                <div>
                    <label class="block text-sm font-medium text-slate-700">API base URL</label>
                    <input type="url" name="paystack_base_url" value="{{ old('paystack_base_url', $paystack_base_url_saved ?? '') }}" placeholder="https://api.paystack.co" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[rgba(14,165,168,.25)]">
                    <p class="text-xs text-slate-500 mt-1">Effective now: {{ $paystack_effective_base_url ?? '—' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Currency code</label>
                    <input type="text" name="paystack_currency" value="{{ old('paystack_currency', $paystack_currency_saved ?? '') }}" placeholder="GHS" maxlength="3" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[rgba(14,165,168,.25)]">
                    <p class="text-xs text-slate-500 mt-1">Effective now: {{ $paystack_effective_currency ?? 'GHS' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">MoMo bank code (transfers)</label>
                    <input type="text" name="paystack_momo_bank_code" value="{{ old('paystack_momo_bank_code', $paystack_momo_bank_code_saved ?? '') }}" placeholder="MTN" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[rgba(14,165,168,.25)]">
                    <p class="text-xs text-slate-500 mt-1">From Paystack (Ghana). Withdrawals to MoMo use this bank code.</p>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" class="btn btn-soft js-close-modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-unfreeze" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
    <div class="w-full max-w-2xl rounded-2xl bg-white shadow-xl border border-slate-200 p-6 text-slate-900">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Support link</h3>
                <p class="text-xs text-slate-500 mt-1">Accepts domain only; auto-prefixes https:// when saving.</p>
            </div>
            <button type="button" class="text-slate-500 hover:text-slate-700 js-close-modal">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-5 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="section" value="unfreeze">
            <div>
                <label class="block text-sm font-medium text-slate-700">Messaging app / website URL</label>
                <input type="text" name="account_unfreeze_petition_url" value="{{ old('account_unfreeze_petition_url', $account_unfreeze_petition_url ?? '') }}" placeholder=".................................com" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[rgba(14,165,168,.25)]">
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" class="btn btn-soft js-close-modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
(() => {
    const backdrop = document.getElementById('settings-backdrop');
    const modals = ['modal-account','modal-limits','modal-paystack','modal-unfreeze']
        .map(id => document.getElementById(id))
        .filter(Boolean);

    const openModal = (id) => {
        const el = document.getElementById(id);
        if (!el) return;
        modals.forEach(m => { m.classList.add('hidden'); m.classList.remove('flex'); });
        backdrop.classList.remove('hidden');
        el.classList.remove('hidden');
        el.classList.add('flex');
    };
    const closeAll = () => {
        backdrop.classList.add('hidden');
        modals.forEach(m => { m.classList.add('hidden'); m.classList.remove('flex'); });
    };

    document.querySelectorAll('.js-open-modal').forEach(btn => {
        btn.addEventListener('click', () => openModal(btn.dataset.modal));
    });
    document.querySelectorAll('.js-close-modal').forEach(btn => {
        btn.addEventListener('click', closeAll);
    });
    backdrop?.addEventListener('click', closeAll);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeAll(); });

    const auto = @json(old('section', ''));
    if (auto) {
        const map = { account: 'modal-account', limits: 'modal-limits', paystack: 'modal-paystack', unfreeze: 'modal-unfreeze' };
        if (map[auto]) openModal(map[auto]);
    }
})();
</script>
@endsection
