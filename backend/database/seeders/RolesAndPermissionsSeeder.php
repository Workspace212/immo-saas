<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    private const GUARD = 'web';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->permissions() as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => self::GUARD,
            ]);
        }

        foreach ($this->rolePermissions() as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => self::GUARD,
            ]);

            $role->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @return list<string>
     */
    private function permissions(): array
    {
        return array_values(array_unique(array_merge(
            $this->tenantBusinessPermissions(),
            [
                'platform.agencies.manage',
                'platform.subscriptions.manage',
                'platform.support.manage',
                'users.viewAny',
                'users.view',
                'users.create',
                'users.update',
                'users.archive',
                'users.restore',
                'roles.viewAny',
                'roles.view',
                'roles.create',
                'roles.update',
                'roles.manage',
                'settings.view',
                'settings.update',
                'settings.manage',
                'audit.viewAny',
                'audit.view',
            ],
        )));
    }

    /**
     * @return list<string>
     */
    private function tenantBusinessPermissions(): array
    {
        return [
            'properties.viewAny',
            'properties.view',
            'properties.create',
            'properties.update',
            'properties.archive',
            'properties.restore',
            'properties.delete',
            'properties.export',
            'owners.viewAny',
            'owners.view',
            'owners.create',
            'owners.update',
            'owners.archive',
            'owners.restore',
            'owners.delete',
            'clients.viewAny',
            'clients.view',
            'clients.create',
            'clients.update',
            'clients.archive',
            'clients.restore',
            'clients.delete',
            'providers.viewAny',
            'providers.view',
            'providers.create',
            'providers.update',
            'providers.archive',
            'providers.restore',
            'providers.delete',
            'mandates.viewAny',
            'mandates.view',
            'mandates.create',
            'mandates.update',
            'mandates.archive',
            'mandates.restore',
            'mandates.delete',
            'contracts.viewAny',
            'contracts.view',
            'contracts.create',
            'contracts.update',
            'contracts.archive',
            'contracts.restore',
            'contracts.delete',
            'contracts.validate',
            'rentals.viewAny',
            'rentals.view',
            'rentals.create',
            'rentals.update',
            'rentals.archive',
            'rentals.restore',
            'rentals.delete',
            'complaints.viewAny',
            'complaints.view',
            'complaints.create',
            'complaints.update',
            'complaints.archive',
            'complaints.restore',
            'complaints.delete',
            'complaints.manage',
            'inspections.viewAny',
            'inspections.view',
            'inspections.create',
            'inspections.update',
            'inspections.archive',
            'inspections.restore',
            'inspections.delete',
            'appointments.viewAny',
            'appointments.view',
            'appointments.create',
            'appointments.update',
            'appointments.archive',
            'appointments.restore',
            'appointments.delete',
            'collaborations.viewAny',
            'collaborations.view',
            'collaborations.create',
            'collaborations.update',
            'collaborations.archive',
            'collaborations.restore',
            'collaborations.delete',
            'collaborations.share',
            'accounting.viewAny',
            'accounting.view',
            'accounting.create',
            'accounting.update',
            'accounting.validate',
            'accounting.manage',
            'accounting.export',
            'accounting.commissions.view',
            'accounting.commissions.manage',
            'invoices.viewAny',
            'invoices.view',
            'invoices.create',
            'invoices.update',
            'invoices.archive',
            'invoices.restore',
            'invoices.delete',
            'invoices.validate',
            'invoices.export',
            'dashboard.view',
            'dashboard.manage',
            'reports.viewAny',
            'reports.view',
            'reports.create',
            'reports.update',
            'reports.archive',
            'reports.restore',
            'reports.delete',
            'reports.export',
            'reports.share',
            'reports.manage',
            'notifications.viewAny',
            'notifications.view',
            'notifications.create',
            'notifications.update',
            'notifications.archive',
            'notifications.restore',
            'notifications.delete',
            'notifications.manage',
            'search.view',
            'search.manage',
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function rolePermissions(): array
    {
        $manager = array_values(array_diff($this->permissions(), [
            'platform.agencies.manage',
            'platform.subscriptions.manage',
            'platform.support.manage',
        ]));

        $assistant = [
            'properties.viewAny',
            'properties.view',
            'properties.create',
            'properties.update',
            'properties.archive',
            'owners.viewAny',
            'owners.view',
            'owners.create',
            'owners.update',
            'clients.viewAny',
            'clients.view',
            'clients.create',
            'clients.update',
            'providers.viewAny',
            'providers.view',
            'providers.create',
            'providers.update',
            'mandates.viewAny',
            'mandates.view',
            'mandates.create',
            'mandates.update',
            'contracts.viewAny',
            'contracts.view',
            'contracts.create',
            'contracts.update',
            'rentals.viewAny',
            'rentals.view',
            'rentals.create',
            'rentals.update',
            'complaints.viewAny',
            'complaints.view',
            'complaints.create',
            'complaints.update',
            'complaints.manage',
            'inspections.viewAny',
            'inspections.view',
            'inspections.create',
            'inspections.update',
            'appointments.viewAny',
            'appointments.view',
            'appointments.create',
            'appointments.update',
            'collaborations.viewAny',
            'collaborations.view',
            'collaborations.create',
            'collaborations.update',
            'invoices.viewAny',
            'invoices.view',
            'invoices.create',
            'invoices.update',
            'dashboard.view',
            'notifications.viewAny',
            'notifications.view',
            'notifications.create',
            'notifications.update',
            'search.view',
        ];

        $agent = [
            'properties.viewAny',
            'properties.view',
            'properties.create',
            'properties.update',
            'owners.viewAny',
            'owners.view',
            'clients.viewAny',
            'clients.view',
            'clients.create',
            'clients.update',
            'mandates.viewAny',
            'mandates.view',
            'mandates.create',
            'mandates.update',
            'contracts.viewAny',
            'contracts.view',
            'contracts.create',
            'contracts.update',
            'rentals.viewAny',
            'rentals.view',
            'complaints.viewAny',
            'complaints.view',
            'complaints.create',
            'complaints.update',
            'inspections.viewAny',
            'inspections.view',
            'inspections.create',
            'inspections.update',
            'appointments.viewAny',
            'appointments.view',
            'appointments.create',
            'appointments.update',
            'collaborations.viewAny',
            'collaborations.view',
            'collaborations.create',
            'collaborations.update',
            'dashboard.view',
            'notifications.viewAny',
            'notifications.view',
            'search.view',
        ];

        return [
            'super_admin' => [
                'platform.agencies.manage',
                'platform.subscriptions.manage',
                'platform.support.manage',
                'users.viewAny',
                'users.view',
                'roles.viewAny',
                'roles.view',
                'settings.view',
                'audit.viewAny',
                'audit.view',
            ],
            'manager' => $manager,
            'assistant' => $assistant,
            'agent' => $agent,
            'employee' => [],
            'owner' => [
                'properties.view',
                'owners.view',
                'contracts.view',
                'rentals.view',
                'invoices.view',
                'complaints.view',
                'complaints.create',
                'appointments.view',
                'notifications.view',
                'dashboard.view',
                'search.view',
            ],
            'client' => [
                'properties.view',
                'clients.view',
                'contracts.view',
                'rentals.view',
                'invoices.view',
                'complaints.view',
                'complaints.create',
                'appointments.view',
                'appointments.create',
                'notifications.view',
                'dashboard.view',
                'search.view',
            ],
            'provider' => [
                'providers.view',
                'complaints.view',
                'complaints.update',
                'appointments.view',
                'notifications.view',
                'dashboard.view',
                'search.view',
            ],
        ];
    }
}
