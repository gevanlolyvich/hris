<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ManagePerformanceTypeFeature extends Seeder
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
                "name" => "Manage Performance Type",
                "guard_name" => "web",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
        ];
        Permission::insert($newFeaturePermissionDatas);

        $newFeatures = [
            ["name" => "Manage Performance Type"],
        ];

        $company = Role::findByName('company');
        $company->givePermissionTo($newFeatures);
    }
}
