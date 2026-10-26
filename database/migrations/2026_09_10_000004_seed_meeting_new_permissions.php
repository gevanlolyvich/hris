<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SeedMeetingNewPermissions extends Migration
{
    public function up()
    {
        $permissions = [
            'Manage Meeting New',
            'Create Meeting New',
            'Edit Meeting New',
            'Delete Meeting New',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $companyRole = Role::where('name', 'company')->first();
        if ($companyRole) {
            $companyRole->givePermissionTo($permissions);
        }

        $employeeRole = Role::where('name', 'employee')->first();
        if ($employeeRole) {
            $employeeRole->givePermissionTo('Manage Meeting New');
        }
    }

    public function down()
    {
        $permissions = [
            'Manage Meeting New',
            'Create Meeting New',
            'Edit Meeting New',
            'Delete Meeting New',
        ];

        foreach ($permissions as $perm) {
            $permission = Permission::where('name', $perm)->first();
            if ($permission) {
                $permission->delete();
            }
        }
    }
}
