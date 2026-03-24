<?php

namespace App\Http\Controllers;

use App\Services\WalletService;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(protected WalletService $walletService) {}

    public function show(): View
    {
        $user = auth()->user();
        $wallet = $this->walletService->getOrCreateWallet($user);
        $transactions = $wallet->transactions()->orderByDesc('created_at')->paginate(30);
        return view('wallet.show', compact('wallet', 'transactions'));
    }
}
