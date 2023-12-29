<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $banks = [
            ['name' => 'Bank BNI', 'code' => '009', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Bank BRI', 'code' => '002', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Bank BTN', 'code' => '200', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Bank BCA', 'code' => '014', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Bank BJB', 'code' => '110', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Bank DKI', 'code' => '111', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ];
        DB::table('banks')->insert($banks);
    }
}
