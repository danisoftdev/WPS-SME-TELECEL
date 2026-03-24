<?php

namespace App\Http\Controllers;

use App\Models\WithdrawalRequest;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WithdrawalRequestController extends Controller
{
    public function __construct(protected WalletService $walletService)
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if ($request->user()?->isSupplier()) {
                abort(403, 'Withdrawals are not available for super admin accounts.');
            }

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $requests = $request->user()
            ->withdrawalRequests()
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('withdrawals.index', compact('requests'));
    }

    public function create(): View|RedirectResponse
    {
        $user = auth()->user();
        if (! $user->ownsAnyStore()) {
            return redirect()->route('stores.index')
                ->with('info', 'Only store owners can request withdrawals.');
        }
        if (! $user->momo_phone || ! $user->momo_account_name) {
            return redirect()->route('store-owner.profile')
                ->with('info', 'Add your MoMo number and account name in your store owner profile before requesting a withdrawal.');
        }

        $this->walletService->getOrCreateWallet($user);
        $user->load('wallet');

        return view('withdrawals.create', ['user' => $user]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user->ownsAnyStore()) {
            return redirect()->route('stores.index')->withErrors(['amount' => 'Only store owners can request withdrawals.']);
        }
        if (! $user->momo_phone || ! $user->momo_account_name) {
            return redirect()->route('store-owner.profile')->withErrors(['momo' => 'Complete your store owner payout profile first.']);
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $amount = round((float) $request->amount, 2);
        $wallet = $this->walletService->getOrCreateWallet($user);

        if ($wallet->is_frozen) {
            return back()->withErrors(['amount' => 'Wallet is frozen.']);
        }

        if ((float) $wallet->balance < $amount) {
            return back()->withErrors(['amount' => 'Insufficient wallet balance.']);
        }

        $pending = WithdrawalRequest::query()
            ->where('user_id', $user->id)
            ->where('status', WithdrawalRequest::STATUS_PENDING)
            ->exists();
        if ($pending) {
            return back()->withErrors(['amount' => 'You already have a pending withdrawal request.']);
        }

        WithdrawalRequest::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'status' => WithdrawalRequest::STATUS_PENDING,
            'momo_phone' => $user->momo_phone,
            'momo_account_name' => $user->momo_account_name,
        ]);

        return redirect()->route('withdrawals.index')->with('success', 'Withdrawal request submitted. An admin will review it.');
    }
}
