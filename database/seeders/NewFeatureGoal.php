<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NewFeatureGoal extends Seeder
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
                "name" => "Manage Goal",
                "guard_name" => "web",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                "name" => "Create Goal",
                "guard_name" => "web",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                "name" => "Edit Goal",
                "guard_name" => "web",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                "name" => "Delete Goal",
                "guard_name" => "web",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                "name" => "Progress Goal",
                "guard_name" => "web",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
        ];
        Permission::insert($newFeaturePermissionDatas);

        $newFeatures = [
            ["name" => "Manage Goal"],
            ["name" => "Create Goal"],
            ["name" => "Edit Goal"],
            ["name" => "Delete Goal"],
            ["name" => "Progress Goal"]
        ];

        $company = Role::findByName('company');
        $company->givePermissionTo($newFeatures);
    }
}
