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
            ['name'=>'Present'],
            ['name'=>'Absent'],
            ['name'=>'Permission'],
            ['name'=>'Leave']
        ];
        DB::table('attendance_statuses')->insert($attendance_statuses);
    }
}
