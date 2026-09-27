<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NewFeatureBudget extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $names = [
            'Manage Budget',
            'Create Budget',
            'Edit Budget',
            'Delete Budget',
        ];

        $now = date('Y-m-d H:i:s');

        $permissionDatas = collect($names)->map(function ($name) use ($now) {
            return [
                'name'       => $name,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        });

        $existing = Permission::whereIn('name', $names)->pluck('name')->toArray();

        $toInsert = $permissionDatas->filter(function ($data) use ($existing) {
            return !in_array($data['name'], $existing);
        });

        if ($toInsert->isNotEmpty()) {
            Permission::insert($toInsert->values()->toArray());
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $newFeatures = collect($names)->map(function ($name) {
            return ['name' => $name];
        })->all();

        $company = Role::findByName('company');
        $company->givePermissionTo($newFeatures);

        $hr = Role::findByName('hr');
        $hr->givePermissionTo($newFeatures);
    }
}
