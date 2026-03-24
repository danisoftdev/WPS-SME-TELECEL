<?php

namespace App\Support;

use App\Models\User;

class ApiTokenAbilities
{
    /**
     * @return list<string>
     */
    public static function forUser(User $user): array
    {
        $abilities = ['account:read', 'wallet:read', 'wallet:transactions:read', 'orders:read'];

        if ($user->can('manage_reseller_plans')) {
            $abilities[] = 'plans:read';
            $abilities[] = 'plans:subscriptions:read';
        }

        if ($user->can('place_orders')) {
            $abilities[] = 'orders:write';
        }

        // Stores, guest-shop parity, MoMo payout & withdrawals, Paystack wallet top-up (same capabilities as web for API-enabled accounts)
        $abilities[] = 'stores:read';
        $abilities[] = 'stores:write';
        $abilities[] = 'payout:read';
        $abilities[] = 'payout:write';
        $abilities[] = 'withdrawals:read';
        $abilities[] = 'withdrawals:write';
        $abilities[] = 'wallet:topup';

        return array_values(array_unique($abilities));
    }
}
