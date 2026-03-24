<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';

        Permission::query()->where('guard_name', $guard)->delete();
        Role::query()->where('guard_name', $guard)->delete();

        $permissions = [
            'manage_users',
            'manage_roles',
            'manage_bundles',
            'manage_orders',
            'update_order_status',
            'credit_wallet',
            'debit_wallet',
            'freeze_wallet',
            'view_all_orders',
            'view_wallet_logs',
            'place_orders',
            'manage_reseller_plans',
            'manage_notifications',
            'access_api',
            'manage_system_settings',
            'manage_stores',
            'set_sub_agent_prices',
            'manage_withdrawals',
        ];

        foreach ($permissions as $name) {
            Permission::create(['name' => $name, 'guard_name' => $guard]);
        }

        // Supplier = Super Admin
        $supplier = Role::create(['name' => 'Supplier', 'guard_name' => $guard]);
        $supplier->givePermissionTo(Permission::all());

        // Admin = normal admin (no user management, no wallets)
        $admin = Role::create(['name' => 'Admin', 'guard_name' => $guard]);
        $admin->givePermissionTo([
            'view_all_orders',
            'manage_orders',
            'update_order_status',
            'manage_bundles',
            'manage_notifications',
            'manage_system_settings',
            'manage_withdrawals',
        ]);

        $wholesaler = Role::create(['name' => 'Wholesaler', 'guard_name' => $guard]);
        $wholesaler->givePermissionTo(['place_orders', 'manage_reseller_plans', 'manage_stores', 'access_api']);

        $retailer = Role::create(['name' => 'Retailer', 'guard_name' => $guard]);
        $retailer->givePermissionTo(['place_orders', 'manage_reseller_plans', 'manage_stores', 'access_api']);

        $subAgent = Role::create(['name' => 'SubAgent', 'guard_name' => $guard]);
        $subAgent->givePermissionTo(['place_orders', 'manage_reseller_plans', 'set_sub_agent_prices', 'access_api']);
    }
}
