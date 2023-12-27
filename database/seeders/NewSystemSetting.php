<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NewSystemSetting extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $new_settings = [
            ['name'=>'late_tolerance', 'value'=>'30', 'created_by'=>1, 'created_at'=>date('Y-m-d H:i:s'), 'updated_at'=>date('Y-m-d H:i:s')],
            ['name'=>'photo_on_clock', 'value'=>'Required', 'created_by'=>1, 'created_at'=>date('Y-m-d H:i:s'), 'updated_at'=>date('Y-m-d H:i:s')],
            ['name'=>'map_tile_url', 'value'=>'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', 'created_by'=>1, 'created_at'=>date('Y-m-d H:i:s'), 'updated_at'=>date('Y-m-d H:i:s')],
        ];
        DB::table('settings')->insert($new_settings);
    }
}
