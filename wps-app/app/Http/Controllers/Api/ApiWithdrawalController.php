<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiWithdrawalController extends Controller
{
    public function __construct(protected WalletService $walletService) {}

    public function index(Request $request): JsonResponse
    {
        if ($request->user()->isSupplier()) {
            abort(403, 'Withdrawals are not available for super admin accounts.');
        }

        $limit = min(100, max(1, $request->integer('limit', 50)));
        $rows = $request->user()
            ->withdrawalRequests()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (WithdrawalRequest $r) => $this->formatWithdrawal($r));

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->isSupplier()) {
            abort(403);
        }
        if (! $user->ownsAnyStore()) {
            return response()->json([
                'message' => 'Withdrawals are for store owners. Create a store and complete MoMo in store owner profile.',
            ], 422);
        }
        if (! $user->momo_phone || ! $user->momo_account_name) {
            return response()->json([
                'message' => 'Complete MoMo details (store owner profile) before requesting a withdrawal.',
            ], 422);
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $amount = round((float) $request->amount, 2);
        $wallet = $this->walletService->getOrCreateWallet($user);

        if ($wallet->is_frozen) {
            return response()->json(['message' => 'Wallet is frozen.'], 422);
        }

        if ((float) $wallet->balance < $amount) {
            return response()->json(['message' => 'Insufficient wallet balance.'], 422);
        }

        if (WithdrawalRequest::query()
            ->where('user_id', $user->id)
            ->where('status', WithdrawalRequest::STATUS_PENDING)
            ->exists()) {
            return response()->json(['message' => 'You already have a pending withdrawal request.'], 422);
        }

        $row = WithdrawalRequest::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'status' => WithdrawalRequest::STATUS_PENDING,
            'momo_phone' => $user->momo_phone,
            'momo_account_name' => $user->momo_account_name,
        ]);

        return response()->json([
            'message' => 'Withdrawal request submitted.',
            'withdrawal' => $this->formatWithdrawal($row),
        ], 201);
    }

    private function formatWithdrawal(WithdrawalRequest $r): array
    {
        return [
            'id' => $r->id,
            'amount' => (float) $r->amount,
            'status' => $r->status,
            'momo_phone' => $r->momo_phone,
            'momo_account_name' => $r->momo_account_name,
            'rejection_reason' => $r->rejection_reason,
            'created_at' => $r->created_at->toIso8601String(),
        ];
    }
}
