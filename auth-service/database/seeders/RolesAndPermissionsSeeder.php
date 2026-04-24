<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Clear permission cache before seeding
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permissions ───────────────────────────────────────────────────────

        $permissions = [
            // Event management
            'events.view',
            'events.create',
            'events.edit',
            'events.delete',
            'events.publish',

            // Kiosk flow
            'kiosk.access',
            'kiosk.check-in',
            'kiosk.manage',
            'kiosk.print-badge',

            // Tournament
            'tournaments.view',
            'tournaments.create',
            'tournaments.edit',
            'tournaments.delete',
            'tournaments.manage-stages',
            'tournaments.manage-brackets',

            // User management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.assign-roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
        }

        // ── Roles ─────────────────────────────────────────────────────────────

        $superAdmin    = Role::firstOrCreate(['name' => 'super-admin',     'guard_name' => 'api']);
        $admin         = Role::firstOrCreate(['name' => 'admin',           'guard_name' => 'api']);
        $organizer     = Role::firstOrCreate(['name' => 'organizer',       'guard_name' => 'api']);
        $kioskOperator = Role::firstOrCreate(['name' => 'kiosk-operator',  'guard_name' => 'api']);
        $participant   = Role::firstOrCreate(['name' => 'participant',     'guard_name' => 'api']);

        // ── Assign permissions to roles ───────────────────────────────────────

        // super-admin: everything
        $superAdmin->givePermissionTo(Permission::where('guard_name', 'api')->get());

        // admin: all except super-admin-level user control
        $admin->givePermissionTo([
            'events.view', 'events.create', 'events.edit', 'events.publish',
            'kiosk.access', 'kiosk.check-in', 'kiosk.manage', 'kiosk.print-badge',
            'tournaments.view', 'tournaments.create', 'tournaments.edit',
            'tournaments.manage-stages', 'tournaments.manage-brackets',
            'users.view', 'users.edit',
        ]);

        // organizer: run events and tournaments, basic kiosk access
        $organizer->givePermissionTo([
            'events.view', 'events.create', 'events.edit', 'events.publish',
            'tournaments.view', 'tournaments.create', 'tournaments.edit',
            'tournaments.manage-stages', 'tournaments.manage-brackets',
            'kiosk.access',
        ]);

        // kiosk-operator: on-site check-in only
        $kioskOperator->givePermissionTo([
            'kiosk.access', 'kiosk.check-in', 'kiosk.print-badge',
            'events.view',
        ]);

        // participant: read-only access
        $participant->givePermissionTo([
            'events.view',
            'tournaments.view',
        ]);

        $this->command->info('Roles and permissions seeded successfully.');
    }
}
