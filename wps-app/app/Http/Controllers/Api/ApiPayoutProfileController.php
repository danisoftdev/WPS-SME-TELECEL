<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiPayoutProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->isSupplier()) {
            abort(403, 'MoMo payout is not used for super admin accounts.');
        }
        if (! $user->ownsAnyStore()) {
            abort(403, 'Store owner profile (MoMo) is only for users who own at least one store.');
        }

        return response()->json([
            'momo_phone' => $user->momo_phone,
            'momo_account_name' => $user->momo_account_name,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->isSupplier()) {
            abort(403);
        }
        if (! $user->ownsAnyStore()) {
            abort(403, 'Create a store before saving MoMo payout details.');
        }

        $request->validate([
            'momo_phone' => 'required|string|max:32',
            'momo_account_name' => 'required|string|max:120',
        ]);

        $request->user()->update([
            'momo_phone' => $request->momo_phone,
            'momo_account_name' => $request->momo_account_name,
        ]);

        return response()->json([
            'message' => 'Payout details saved.',
            'momo_phone' => $request->user()->momo_phone,
            'momo_account_name' => $request->user()->momo_account_name,
        ]);
    }
}
