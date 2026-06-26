<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'manage_applications',
            'manage_cycles',
            'manage_scheduling',
            'manage_screening',
            'manage_selection',
            'manage_reports',
            'manage_announcements',
            'manage_users',
            'manage_settings',
            'manage_backups',
            'manage_security',
            'view_audit_logs',
            'export_data',
            'send_notifications',
        ];

        foreach ($permissions as $perm) {
            Permission::create(['name' => $perm, 'guard_name' => 'web']);
        }

        $superAdmin = Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        $roles = [
            'admin' => ['manage_applications', 'manage_cycles', 'manage_scheduling', 'manage_screening', 'manage_selection', 'manage_reports', 'manage_announcements', 'view_audit_logs', 'export_data', 'send_notifications'],
            'recruitment_officer' => ['manage_applications', 'manage_cycles', 'manage_selection'],
            'screening_officer' => ['manage_screening', 'manage_applications'],
            'scheduling_officer' => ['manage_scheduling', 'manage_applications'],
        ];

        foreach ($roles as $name => $perms) {
            $role = Role::create(['name' => $name, 'guard_name' => 'web']);
            $role->givePermissionTo($perms);
        }
    }
}
