<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attendance_statuses = [
            ['name'=>'Present','label'=>'H'],
            ['name'=>'Absent','label'=>'A'],
            ['name'=>'Permission','label'=>'I'],
            ['name'=>'Leave','label'=>'C']
        ];
        DB::table('attendance_statuses')->insert($attendance_statuses);
    }
}
