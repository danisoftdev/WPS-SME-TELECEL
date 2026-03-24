<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminWalletController extends Controller
{
    public function __construct(protected WalletService $walletService)
    {
        $this->middleware('permission:credit_wallet|debit_wallet|view_wallet_logs');
    }

    public function index(Request $request): View
    {
        $query = User::with('wallet');
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn ($qry) => $qry->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
        }
        $users = $query->paginate(20)->withQueryString();
        return view('admin.wallets.index', compact('users'));
    }

    public function show(User $user): View
    {
        $wallet = $this->walletService->getOrCreateWallet($user);
        $wallet->load('user');
        $transactions = $wallet->transactions()->with('performer')->paginate(30);
        return view('admin.wallets.show', compact('wallet', 'transactions'));
    }

    public function credit(Request $request, User $user): RedirectResponse
    {
        $request->validate(['amount' => 'required|numeric|min:0.01', 'description' => 'nullable|string|max:255']);
        $this->walletService->credit($user, (float) $request->amount, 'admin', null, $request->description, $request->user());
        return back()->with('success', 'Wallet credited.');
    }

    public function debit(Request $request, User $user): RedirectResponse
    {
        $request->validate(['amount' => 'required|numeric|min:0.01', 'description' => 'nullable|string|max:255']);
        $this->walletService->debit($user, (float) $request->amount, 'admin', null, $request->description, $request->user());
        return back()->with('success', 'Wallet debited.');
    }

    public function freeze(User $user): RedirectResponse
    {
        $this->walletService->setFrozen($user, true);
        return back()->with('success', 'Wallet frozen.');
    }

    public function unfreeze(User $user): RedirectResponse
    {
        $this->walletService->setFrozen($user, false);
        return back()->with('success', 'Wallet unfrozen.');
    }
}
