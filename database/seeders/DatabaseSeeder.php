<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UsersTableSeeder::class,
            AttendanceStatusSeeder::class,
            NewFeature::class,
            NewSystemSetting::class,
            BankSeeder::class,
            NewFeatureBank::class,
            NewFeatureLevel::class,
            NewFeatureGoal::class,
            ManagePerformanceTypeFeature::class,
            NewSystemTaxSetting::class,
        ]);
    }
}
