<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NewFeatureHealthyTarget extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currentTimestamp = date('Y-m-d H:i:s');
        $featureNames = [
            "Manage Healthy Target",
            "Create Healthy Target",
            "Edit Healthy Target",
            "Delete Healthy Target"
        ];

        $newFeature = array_map(function ($name) use ($currentTimestamp) {
            return [
                "name" => $name,
                "guard_name" => "web",
                "created_at" => $currentTimestamp,
                "updated_at" => $currentTimestamp,
            ];
        }, $featureNames);

        Permission::insert($newFeature);

        $transformNewFeature = array_map(fn ($item) => ["name" => $item["name"]], $newFeature);

        $company = Role::findByName('company');
        $company->givePermissionTo($transformNewFeature);

        $hr = Role::findByName('hr');
        $hr->givePermissionTo($transformNewFeature);
    }
}
