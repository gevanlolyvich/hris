<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NewFeatureBpjs extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $newFeaturePermissionDatas = [
            [
                "name" => "Manage Bpjs Option",
                "guard_name" => "web",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                "name" => "Create Bpjs Option",
                "guard_name" => "web",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                "name" => "Edit Bpjs Option",
                "guard_name" => "web",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                "name" => "Delete Bpjs Option",
                "guard_name" => "web",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                "name" => "Manage Bpjs",
                "guard_name" => "web",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                "name" => "Create Bpjs",
                "guard_name" => "web",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                "name" => "Edit Bpjs",
                "guard_name" => "web",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                "name" => "Delete Bpjs",
                "guard_name" => "web",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
        ];

        $existing = Permission::whereIn('name', [
            'Manage Bpjs Option', 'Create Bpjs Option', 'Edit Bpjs Option', 'Delete Bpjs Option',
            'Manage Bpjs', 'Create Bpjs', 'Edit Bpjs', 'Delete Bpjs',
        ])->pluck('name')->toArray();

        $toInsert = collect($newFeaturePermissionDatas)->filter(function ($data) use ($existing) {
            return !in_array($data['name'], $existing);
        });

        if ($toInsert->isNotEmpty()) {
            Permission::insert($toInsert->values()->toArray());
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $newFeatures = [
            ["name" => "Manage Bpjs Option"],
            ["name" => "Create Bpjs Option"],
            ["name" => "Edit Bpjs Option"],
            ["name" => "Delete Bpjs Option"],
            ["name" => "Manage Bpjs"],
            ["name" => "Create Bpjs"],
            ["name" => "Edit Bpjs"],
            ["name" => "Delete Bpjs"],
        ];

        $company = Role::findByName('company');
        $company->givePermissionTo($newFeatures);

        $hr = Role::findByName('hr');
        $hr->givePermissionTo($newFeatures);
    }
}