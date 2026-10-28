<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    protected array $permissions = [
        'Manage Flexible Time',
        'Create Flexible Time',
        'Approval Flexible Time',
    ];

    public function up(): void
    {
        foreach ($this->permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        foreach (['company', 'hr'] as $roleName) {
            $role = Role::findByName($roleName, 'web');
            if ($role) {
                $role->givePermissionTo($this->permissions);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->permissions as $name) {
            Permission::where('name', $name)->delete();
        }
    }
};
