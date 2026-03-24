<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Idempotent: safe for production upgrades without re-running RolesAndPermissionsSeeder.
 */
class SaaSPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';

        foreach (['manage_stores', 'set_sub_agent_prices'] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
        }

        Permission::firstOrCreate(['name' => 'access_api', 'guard_name' => $guard]);
        Permission::firstOrCreate(['name' => 'manage_withdrawals', 'guard_name' => $guard]);

        $adminRole = Role::query()->where('name', 'Admin')->where('guard_name', $guard)->first();
        if ($adminRole && ! $adminRole->hasPermissionTo('manage_withdrawals')) {
            $adminRole->givePermissionTo('manage_withdrawals');
        }

        foreach (['Wholesaler', 'Retailer'] as $roleName) {
            $role = Role::query()->where('name', $roleName)->where('guard_name', $guard)->first();
            if ($role && ! $role->hasPermissionTo('manage_stores')) {
                $role->givePermissionTo('manage_stores');
            }
            if ($role && ! $role->hasPermissionTo('access_api')) {
                $role->givePermissionTo('access_api');
            }
        }

        $sub = Role::firstOrCreate(['name' => 'SubAgent', 'guard_name' => $guard]);
        foreach (['place_orders', 'manage_reseller_plans', 'set_sub_agent_prices', 'access_api'] as $perm) {
            if (! $sub->hasPermissionTo($perm)) {
                $sub->givePermissionTo($perm);
            }
        }
    }
}
