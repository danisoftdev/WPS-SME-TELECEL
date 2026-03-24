<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ApiTokenAbilities;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ApiAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::guard('web')->attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.']]);
        }

        $user = Auth::guard('web')->user();
        if ($user->is_frozen) {
            Auth::guard('web')->logout();
            throw ValidationException::withMessages(['email' => ['Account is frozen.']]);
        }

        if (! $user->can('access_api')) {
            Auth::guard('web')->logout();
            throw ValidationException::withMessages(['email' => ['API access is not enabled for this account.']]);
        }

        $abilities = ApiTokenAbilities::forUser($user);

        $token = $user->createToken('wps-api', $abilities)->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'abilities' => $abilities,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('wallet');

        $payload = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
            'wallet_balance' => $user->wallet?->balance ?? 0,
            'is_frozen' => $user->is_frozen,
        ];
        if ($user->isSupplier() || ! $user->ownsAnyStore()) {
            $payload['momo_phone'] = null;
            $payload['momo_account_name'] = null;
        } else {
            $payload['momo_phone'] = $user->momo_phone;
            $payload['momo_account_name'] = $user->momo_account_name;
        }

        return response()->json($payload);
    }
}
