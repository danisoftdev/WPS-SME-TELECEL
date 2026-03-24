<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WalletTopup;
use App\Services\PaystackGatewayConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiWalletTopupController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = $request->user();
        if ($user->wallet && $user->wallet->is_frozen) {
            return response()->json(['message' => 'Wallet is frozen. Contact admin.'], 422);
        }

        $amount = round((float) $request->amount, 2);
        $reference = 'WPS-'.Str::upper(Str::random(12)).'-'.time();

        WalletTopup::create([
            'user_id' => $user->id,
            'reference' => $reference,
            'amount' => $amount,
            'currency' => PaystackGatewayConfig::currency(),
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Top-up initiated. Open pay_url to complete payment.',
            'reference' => $reference,
            'amount' => $amount,
            'currency' => PaystackGatewayConfig::currency(),
            'pay_url' => route('wallet.topups.paystack', ['reference' => $reference]),
        ], 201);
    }

    public function show(Request $request, string $reference): JsonResponse
    {
        $topup = WalletTopup::query()->where('reference', $reference)->firstOrFail();

        if ((int) $topup->user_id !== (int) $request->user()->id) {
            abort(404);
        }

        return response()->json([
            'reference' => $topup->reference,
            'amount' => (float) $topup->amount,
            'currency' => $topup->currency,
            'status' => $topup->status,
            'pay_url' => $topup->status === 'pending'
                ? route('wallet.topups.paystack', ['reference' => $reference])
                : null,
            'paid_at' => $topup->paid_at?->toIso8601String(),
            'created_at' => $topup->created_at->toIso8601String(),
        ]);
    }
}
