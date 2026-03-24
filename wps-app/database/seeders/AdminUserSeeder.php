<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\WalletService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@wps-sme.local'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'phone' => null,
                'is_frozen' => false,
                'daily_order_limit' => null,
            ]
        );
        $admin->assignRole('Supplier');

        (new WalletService)->getOrCreateWallet($admin);
    }
}
