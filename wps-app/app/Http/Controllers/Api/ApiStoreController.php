<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ApiStoreController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = min(100, max(1, $request->integer('limit', 50)));
        $stores = $request->user()
            ->ownedStores()
            ->withCount('subAgents')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (Store $s) => $this->formatStore($s));

        return response()->json(['data' => $stores]);
    }

    public function show(Request $request, Store $store): JsonResponse
    {
        $this->authorizeOwner($request, $store);
        $store->loadCount('subAgents');

        return response()->json($this->formatStore($store));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatedPayload($request);
        $slug = $this->makeUniqueSlug(Str::slug($data['name']) !== '' ? Str::slug($data['name']) : 'store');

        $ownerId = (int) $request->user()->id;
        if ($request->user()->isSupplier()) {
            $request->validate([
                'owner_user_id' => 'required|integer|exists:users,id',
            ]);
            $ownerId = (int) $request->owner_user_id;
            if ($ownerId === (int) $request->user()->id) {
                throw ValidationException::withMessages([
                    'owner_user_id' => ['Super admin cannot assign a store to their own account.'],
                ]);
            }
            $owner = User::query()->findOrFail($ownerId);
            if ($owner->isSupplier()) {
                throw ValidationException::withMessages([
                    'owner_user_id' => ['Choose a user account, not another super admin.'],
                ]);
            }
        }

        $store = Store::create([
            'user_id' => $ownerId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'whatsapp_phone' => $data['whatsapp_phone'],
            'contact_email' => $data['contact_email'] ?? null,
            'location' => $data['location'] ?? null,
            'slug' => $slug,
            'invite_token' => Str::random(48),
            'is_active' => $request->boolean('is_active', true),
        ]);
        $store->loadCount('subAgents');

        return response()->json([
            'message' => 'Store created.',
            'store' => $this->formatStore($store),
        ], 201);
    }

    public function update(Request $request, Store $store): JsonResponse
    {
        $this->authorizeOwner($request, $store);
        $data = $this->validatedPayload($request);

        $slug = $store->slug;
        if (Str::slug($data['name']) !== Str::slug($store->name)) {
            $slug = $this->makeUniqueSlug(Str::slug($data['name']) !== '' ? Str::slug($data['name']) : 'store');
        }

        $store->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'whatsapp_phone' => $data['whatsapp_phone'],
            'contact_email' => $data['contact_email'] ?? null,
            'location' => $data['location'] ?? null,
            'slug' => $slug,
            'is_active' => $request->boolean('is_active', $store->is_active),
        ]);
        $store->loadCount('subAgents');

        return response()->json([
            'message' => 'Store updated.',
            'store' => $this->formatStore($store),
        ]);
    }

    public function destroy(Request $request, Store $store): JsonResponse
    {
        $this->authorizeOwner($request, $store);

        if ($store->subAgents()->exists()) {
            return response()->json([
                'message' => 'Remove or reassign sub-agents before deleting this store.',
            ], 422);
        }

        $store->delete();

        return response()->json(['message' => 'Store deleted.']);
    }

    /**
     * @return array{name: string, whatsapp_phone: string, description?: ?string, contact_email?: ?string, location?: ?string}
     */
    private function validatedPayload(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'whatsapp_phone' => 'required|string|max:32',
            'contact_email' => 'nullable|email|max:255',
            'location' => 'nullable|string|max:500',
        ]);
    }

    private function authorizeOwner(Request $request, Store $store): void
    {
        if ((int) $store->user_id !== (int) $request->user()->id) {
            abort(404);
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

    private function formatStore(Store $s): array
    {
        return [
            'id' => $s->id,
            'name' => $s->name,
            'slug' => $s->slug,
            'description' => $s->description,
            'whatsapp_phone' => $s->whatsapp_phone,
            'contact_email' => $s->contact_email,
            'location' => $s->location,
            'logo_url' => $s->seoOgImageUrl(),
            'is_active' => (bool) $s->is_active,
            'customer_shop_url' => $s->customerShopUrl(),
            'invite_url' => url('/join-store/'.$s->invite_token),
            'sub_agents_count' => (int) ($s->sub_agents_count ?? $s->subAgents()->count()),
            'created_at' => $s->created_at->toIso8601String(),
        ];
    }
}
