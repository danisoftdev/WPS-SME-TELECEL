<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use App\Support\StoreLogoUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminStoreController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Supplier']);
    }

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $like = $q !== '' ? '%'.addcslashes($q, '%_\\').'%' : null;

        $stores = Store::query()
            ->with('owner')
            ->when(Schema::hasColumn('users', 'store_id'), fn ($query) => $query->withCount('subAgents'))
            ->when($like !== null, function ($query) use ($like) {
                $query->where(function ($sub) use ($like) {
                    $sub->where('name', 'like', $like)
                        ->orWhere('slug', 'like', $like)
                        ->orWhereHas('owner', function ($oq) use ($like) {
                            $oq->where('name', 'like', $like)->orWhere('email', 'like', $like);
                        });
                });
            })
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.stores.index', compact('stores', 'q'));
    }

    public function create(): View
    {
        $eligibleOwners = $this->eligibleOwnersQuery()->orderBy('name')->get();

        return view('admin.stores.create', compact('eligibleOwners'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'owner_user_id' => 'required|integer|exists:users,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'whatsapp_phone' => 'required|string|max:32',
            'contact_email' => 'nullable|email|max:255',
            'location' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'logo' => StoreLogoUpload::VALIDATION_RULE,
        ]);

        $ownerId = (int) $request->owner_user_id;
        if ($ownerId === (int) $request->user()->id) {
            return back()->withInput()->withErrors(['owner_user_id' => 'You cannot assign a store to your own super admin account.']);
        }

        $owner = User::query()->findOrFail($ownerId);
        if ($owner->isSupplier()) {
            return back()->withInput()->withErrors(['owner_user_id' => 'Choose a user account, not another super admin.']);
        }

        $slug = $this->makeUniqueSlug(Str::slug($request->name) !== '' ? Str::slug($request->name) : 'store');

        $store = Store::create([
            'user_id' => $ownerId,
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

        return redirect()->route('admin.stores.index')->with(
            'success',
            'Store created for '.$owner->name.'. Customer shop: '.$store->customerShopUrl()
        );
    }

    public function edit(Store $store): View
    {
        $eligibleOwners = $this->eligibleOwnersQuery()->orderBy('name')->get();

        return view('admin.stores.edit', compact('store', 'eligibleOwners'));
    }

    public function update(Request $request, Store $store): RedirectResponse
    {
        $request->validate([
            'owner_user_id' => 'required|integer|exists:users,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'whatsapp_phone' => 'required|string|max:32',
            'contact_email' => 'nullable|email|max:255',
            'location' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'logo' => StoreLogoUpload::VALIDATION_RULE,
            'remove_logo' => 'nullable|boolean',
        ]);

        $ownerId = (int) $request->owner_user_id;
        if ($ownerId === (int) $request->user()->id) {
            return back()->withInput()->withErrors(['owner_user_id' => 'Store owner cannot be your super admin account.']);
        }

        $owner = User::query()->findOrFail($ownerId);
        if ($owner->isSupplier()) {
            return back()->withInput()->withErrors(['owner_user_id' => 'Choose a user account, not another super admin.']);
        }

        $slug = $store->slug;
        if (Str::slug($request->name) !== Str::slug($store->name)) {
            $slug = $this->makeUniqueSlug(Str::slug($request->name) !== '' ? Str::slug($request->name) : 'store');
        }

        $payload = [
            'user_id' => $ownerId,
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

        return redirect()->route('admin.stores.index')->with('success', 'Store updated.');
    }

    public function destroy(Store $store): RedirectResponse
    {
        if (Schema::hasColumn('users', 'store_id') && $store->subAgents()->exists()) {
            return back()->withErrors(['delete' => 'Remove or reassign sub-agents before deleting this store.']);
        }

        $store->delete();

        return redirect()->route('admin.stores.index')->with('success', 'Store deleted.');
    }

    /** Users who may own a store (not super admin). */
    private function eligibleOwnersQuery()
    {
        return User::query()->whereDoesntHave('roles', function ($q) {
            $q->where('name', 'Supplier');
        });
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
