<?php

namespace App\Http\Controllers;

use App\Models\ShopCheckout;
use App\Models\Store;
use App\Services\WalletService;
use App\Support\StoreLogoUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(WalletService $walletService): View|RedirectResponse
    {
        if (auth()->user()->isSupplier()) {
            return redirect()->route('admin.stores.index');
        }

        $user = auth()->user();
        $stores = $user->ownedStores()->withCount('subAgents')->orderByDesc('created_at')->paginate(20);

        $wallet = null;
        $shopSalesStats = null;
        if ($user->ownsAnyStore()) {
            $wallet = $walletService->getOrCreateWallet($user);
            $storeIds = $user->ownedStores()->pluck('id');
            $shopSalesStats = [
                'completed_count' => (int) ShopCheckout::query()
                    ->whereIn('store_id', $storeIds)
                    ->where('status', 'success')
                    ->count(),
                'net_earnings_total' => round((float) ShopCheckout::query()
                    ->whereIn('store_id', $storeIds)
                    ->where('status', 'success')
                    ->sum('net_to_merchant'), 2),
            ];
        }

        return view('stores.index', compact('stores', 'wallet', 'shopSalesStats'));
    }

    /**
     * JSON for store owners: wallet + shop sales totals (polled from "My stores" for live balance updates).
     */
    public function walletSnapshot(WalletService $walletService): JsonResponse
    {
        $user = auth()->user();
        abort_if($user->isSupplier(), 403);
        abort_unless($user->ownsAnyStore(), 403);

        $wallet = $walletService->getOrCreateWallet($user);
        $storeIds = $user->ownedStores()->pluck('id');
        $completedCount = (int) ShopCheckout::query()
            ->whereIn('store_id', $storeIds)
            ->where('status', 'success')
            ->count();
        $netEarnings = round((float) ShopCheckout::query()
            ->whereIn('store_id', $storeIds)
            ->where('status', 'success')
            ->sum('net_to_merchant'), 2);

        return response()->json([
            'balance' => round((float) $wallet->balance, 2),
            'balance_formatted' => number_format((float) $wallet->balance, 2),
            'is_frozen' => (bool) $wallet->is_frozen,
            'shop_sales_completed_count' => $completedCount,
            'shop_net_earnings_total' => $netEarnings,
            'shop_net_earnings_formatted' => number_format($netEarnings, 2),
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function create(): View
    {
        abort_if(auth()->user()->isSupplier(), 403, 'Super admin creates stores for users under Admin → Stores for users.');

        return view('stores.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if($request->user()->isSupplier(), 403);
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'whatsapp_phone' => 'required|string|max:32',
            'contact_email' => 'nullable|email|max:255',
            'location' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'logo' => StoreLogoUpload::VALIDATION_RULE,
        ]);

        $slug = $this->makeUniqueSlug(Str::slug($request->name) !== '' ? Str::slug($request->name) : 'store');

        $store = Store::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'description' => $request->description,
            'whatsapp_phone' => $request->whatsapp_phone,
            'contact_email' => $request->contact_email,
            'location' => $request->location,
            'logo_path' => StoreLogoUpload::persistFromRequest($request, null),
            'slug' => $slug,
            'invite_token' => Str::random(48),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('stores.index')->with(
            'success',
            'Store created. Customer shop: '.$store->customerShopUrl().' — set catalog prices under Pricing, then share that link with buyers.'
        );
    }

    public function edit(Store $store): View
    {
        abort_if(auth()->user()->isSupplier(), 403);
        $this->authorizeStore($store);

        return view('stores.edit', compact('store'));
    }

    public function update(Request $request, Store $store): RedirectResponse
    {
        abort_if($request->user()->isSupplier(), 403);
        $this->authorizeStore($store);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'whatsapp_phone' => 'required|string|max:32',
            'contact_email' => 'nullable|email|max:255',
            'location' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'logo' => StoreLogoUpload::VALIDATION_RULE,
            'remove_logo' => 'nullable|boolean',
        ]);

        $slug = $store->slug;
        if (Str::slug($request->name) !== Str::slug($store->name)) {
            $slug = $this->makeUniqueSlug(Str::slug($request->name) !== '' ? Str::slug($request->name) : 'store');
        }

        $payload = [
            'name' => $request->name,
            'description' => $request->description,
            'whatsapp_phone' => $request->whatsapp_phone,
            'contact_email' => $request->contact_email,
            'location' => $request->location,
            'slug' => $slug,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->boolean('remove_logo') && ! $request->hasFile('logo')) {
            StoreLogoUpload::deleteIfPresent($store->logo_path);
            $payload['logo_path'] = null;
        }

        $newLogo = StoreLogoUpload::persistFromRequest($request, $store);
        if ($newLogo !== null) {
            $payload['logo_path'] = $newLogo;
        }

        $store->update($payload);

        return redirect()->route('stores.index')->with(
            'success',
            'Store updated. Customer shop: '.$store->customerShopUrl()
        );
    }

    public function destroy(Store $store): RedirectResponse
    {
        abort_if(auth()->user()->isSupplier(), 403);
        $this->authorizeStore($store);

        if ($store->subAgents()->exists()) {
            return back()->withErrors(['delete' => 'Remove or reassign sub-agents before deleting this store.']);
        }

        $store->delete();

        return redirect()->route('stores.index')->with('success', 'Store deleted.');
    }

    private function authorizeStore(Store $store): void
    {
        if ((int) $store->user_id !== (int) auth()->id()) {
            abort(403);
        }
    }

    private function makeUniqueSlug(string $base): string
    {
        $slug = $base;
        $original = $slug;
        $n = 0;
        while (Store::query()->where('slug', $slug)->exists()) {
            $slug = $original.'-'.(++$n);
        }

        return $slug;
    }
}
