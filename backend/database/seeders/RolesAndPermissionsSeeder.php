<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'manage agency',
            'manage users',
            'view properties',
            'create properties',
            'edit properties',
            'delete properties',
            'search properties',
            'collaborate on properties',
            'view contracts',
            'create contracts',
            'edit contracts',
            'view complaints',
            'create complaints',
            'manage complaints',
            'view complaint status',
            'view invoices',
            'manage invoices',
            'view commissions',
            'manage inspections',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $rolePermissions = [
            'Manager' => $permissions,
            'Assistant' => [
                'view properties',
                'view contracts',
                'view complaints',
                'manage complaints',
                'view invoices',
            ],
            'Agent' => [
                'view properties',
                'create properties',
                'edit properties',
                'search properties',
                'collaborate on properties',
                'view contracts',
                'create contracts',
                'edit contracts',
                'view complaints',
                'manage complaints',
                'view commissions',
            ],
            'Employee' => [
                'view properties',
                'view complaints',
                'manage complaints',
                'manage inspections',
            ],
            'Owner' => [
                'view properties',
                'view contracts',
                'view invoices',
                'view complaint status',
            ],
            'Client' => [
                'view contracts',
                'view invoices',
                'create complaints',
                'view complaint status',
            ],
        ];

        foreach ($rolePermissions as $roleName => $assignedPermissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($assignedPermissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
