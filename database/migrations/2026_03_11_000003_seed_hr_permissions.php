<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $newPermissions = [
            // Staff management
            'staff_view',
            'staff_create',
            'staff_update',
            // Attendance
            'attendance_view',
            'attendance_mark',
            // Payroll
            'payroll_view',
            'payroll_generate',
        ];

        foreach ($newPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Give ALL new permissions to Admin role automatically
        $admin = Role::where('name', 'Admin')->first();
        if ($admin) {
            $admin->givePermissionTo($newPermissions);
        }
    }

    public function down(): void
    {
        $permissions = [
            'staff_view', 'staff_create', 'staff_update',
            'attendance_view', 'attendance_mark',
            'payroll_view', 'payroll_generate',
        ];
        foreach ($permissions as $perm) {
            Permission::where('name', $perm)->delete();
        }
    }
};
