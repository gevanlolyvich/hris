<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NewFeatureLeaveOffice extends Seeder
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
                "name" => "Manage Leave Office",
                "guard_name" => "web",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                "name" => "Create Leave Office",
                "guard_name" => "web",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                "name" => "Edit Leave Office",
                "guard_name" => "web",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                "name" => "Delete Leave Office",
                "guard_name" => "web",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                "name" => "Approval Leave Office",
                "guard_name" => "web",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
        ];
        Permission::insert($newFeaturePermissionDatas);

        $newFeatures = [
            ["name" => "Manage Leave Office"],
            ["name" => "Create Leave Office"],
            ["name" => "Edit Leave Office"],
            ["name" => "Delete Leave Office"],
            ["name" => "Approval Leave Office"]
        ];

        $company = Role::findByName('company');
        $company->givePermissionTo($newFeatures);
    }
}
