<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'max_pending_orders_per_user' => '10',
            'shop_platform_fee_percent' => '0',
        ];
        foreach ($defaults as $key => $value) {
            SystemSetting::set($key, $value);
        }
    }
}
