<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NewFeatureBank extends Seeder
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
                "name" => "Manage Bank",
                "guard_name" => "web",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                "name" => "Create Bank",
                "guard_name" => "web",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                "name" => "Edit Bank",
                "guard_name" => "web",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                "name" => "Delete Bank",
                "guard_name" => "web",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
        ];
        Permission::insert($newFeaturePermissionDatas);

        $newFeatures = [
            ["name" => "Manage Bank"],
            ["name" => "Create Bank"],
            ["name" => "Edit Bank"],
            ["name" => "Delete Bank"]
        ];

        $company = Role::findByName('company');
        $company->givePermissionTo($newFeatures);

        // $hr = Role::findByName('hr');
        // $hr->givePermissionTo($newFeatures);
    }
}
