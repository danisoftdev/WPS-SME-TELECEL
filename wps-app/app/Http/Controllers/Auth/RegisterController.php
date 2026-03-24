<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RegisterController extends Controller
{
    public function __construct(protected WalletService $walletService) {}

    public function showRegistrationForm(Request $request): View
    {
        $inviteToken = $request->query('store_invite');
        if (! is_string($inviteToken) || $inviteToken === '') {
            $inviteToken = old('store_invite');
        }
        $inviteToken = (is_string($inviteToken) && $inviteToken !== '') ? $inviteToken : null;
        $inviteStore = $this->resolveInviteStore($inviteToken);

        return view('auth.register', [
            'storeInviteToken' => $inviteStore !== null ? $inviteToken : null,
            'inviteStore' => $inviteStore,
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $inviteStore = $this->resolveInviteStore($request->input('store_invite'));

        if ($request->filled('store_invite')) {
            if (! $inviteStore) {
                throw ValidationException::withMessages([
                    'store_invite' => ['This invite link is invalid or the store is no longer active.'],
                ]);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'nullable|string|max:20',
                'store_invite' => 'required|string',
            ]);

            $user = DB::transaction(function () use ($request, $inviteStore) {
                $role = $this->ensureSubAgentRoleWithPermissions();

                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => $request->password,
                    'phone' => $request->phone,
                    'store_id' => $inviteStore->id,
                    'parent_user_id' => $inviteStore->user_id,
                ]);

                $user->assignRole($role);
                $this->walletService->getOrCreateWallet($user);

                return $user;
            });

            $this->finishAuthenticatedSession($request, $user);

            return redirect()->intended(route('dashboard'))->with('success', 'Welcome! You joined '.$inviteStore->name.'.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = DB::transaction(function () use ($request) {
            $role = $this->ensureRetailerRoleWithPermissions();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'phone' => $request->phone,
            ]);

            $user->assignRole($role);
            $this->walletService->getOrCreateWallet($user);

            return $user;
        });

        $this->finishAuthenticatedSession($request, $user);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Spatie permission cache is not cleared when package events are disabled; flush so the next
     * request (dashboard middleware, @can, etc.) sees the new role. Login by id after refresh.
     */
    private function finishAuthenticatedSession(Request $request, User $user): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $user->refresh();

        Auth::loginUsingId($user->getKey());
        $request->session()->regenerate();
    }

    private function resolveInviteStore(?string $token): ?Store
    {
        if ($token === null || $token === '') {
            return null;
        }

        try {
            return Store::query()->where('invite_token', $token)->where('is_active', true)->first();
        } catch (QueryException $e) {
            report($e);

            return null;
        }
    }

    /**
     * Idempotent: matches SaaSPermissionsSeeder so registration works if seeders were skipped.
     */
    private function ensureSubAgentRoleWithPermissions(): Role
    {
        $guard = config('auth.defaults.guard');
        $role = Role::firstOrCreate(['name' => 'SubAgent', 'guard_name' => $guard]);

        foreach (['place_orders', 'manage_reseller_plans', 'set_sub_agent_prices', 'access_api'] as $permName) {
            $permission = Permission::firstOrCreate(['name' => $permName, 'guard_name' => $guard]);
            if (! $role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }

        return $role;
    }

    /** Public sign-up is Retailer only; admins assign Wholesaler etc. in Users. */
    private function ensureRetailerRoleWithPermissions(): Role
    {
        $guard = config('auth.defaults.guard');
        $role = Role::firstOrCreate(['name' => 'Retailer', 'guard_name' => $guard]);

        foreach (['place_orders', 'manage_reseller_plans', 'manage_stores', 'access_api'] as $permName) {
            $permission = Permission::firstOrCreate(['name' => $permName, 'guard_name' => $guard]);
            if (! $role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }

        return $role;
    }
}
