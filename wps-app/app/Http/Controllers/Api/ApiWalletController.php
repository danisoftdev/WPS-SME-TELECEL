<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiWalletController extends Controller
{
    public function __construct(protected WalletService $walletService) {}

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);
        return response()->json([
            'balance' => (float) $wallet->balance,
            'is_frozen' => $wallet->is_frozen,
        ]);
    }

    public function transactions(Request $request): JsonResponse
    {
        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);
        $transactions = $wallet->transactions()
            ->orderByDesc('created_at')
            ->limit($request->integer('limit', 50))
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'type' => $t->type,
                'amount' => (float) $t->amount,
                'balance_after' => (float) $t->balance_after,
                'reference_type' => $t->reference_type,
                'reference_id' => $t->reference_id,
                'description' => $t->description,
                'created_at' => $t->created_at->toIso8601String(),
            ]);
        return response()->json(['data' => $transactions]);
    }
}
