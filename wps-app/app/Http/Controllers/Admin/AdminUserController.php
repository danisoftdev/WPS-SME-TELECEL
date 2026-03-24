<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function __construct(protected WalletService $walletService)
    {
        $this->middleware('permission:manage_users');
    }

    public function index(Request $request): View
    {
        $query = User::with('roles', 'wallet');
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn ($qry) => $qry->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
        }
        if ($request->filled('role')) {
            $query->role($request->role);
        }
        $users = $query->paginate(20)->withQueryString();
        $roles = \Spatie\Permission\Models\Role::where('guard_name', 'web')->pluck('name');
        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        $roles = \Spatie\Permission\Models\Role::where('guard_name', 'web')->get();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'daily_order_limit' => 'nullable|integer|min:1',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'daily_order_limit' => $request->daily_order_limit,
        ]);
        $user->assignRole($request->roles);
        $this->walletService->getOrCreateWallet($user);
        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user): View
    {
        if ($user->hasRole('Supplier')) {
            abort(403);
        }
        $user->load('roles', 'wallet');
        $roles = \Spatie\Permission\Models\Role::where('guard_name', 'web')->get();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        if ($user->hasRole('Supplier')) {
            abort(403);
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'daily_order_limit' => 'nullable|integer|min:1',
            'is_frozen' => 'boolean',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);
        $data = ['name' => $request->name, 'email' => $request->email, 'phone' => $request->phone, 'daily_order_limit' => $request->daily_order_limit, 'is_frozen' => $request->boolean('is_frozen')];
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        $user->update($data);
        $user->syncRoles($request->roles);
        if ($request->boolean('is_frozen')) {
            $this->walletService->setFrozen($user, true);
        } else {
            $this->walletService->setFrozen($user, false);
        }
        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->hasRole('Supplier')) {
            abort(403);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }
}
