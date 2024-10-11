<?php

namespace App\Console\Commands;

use App\Http\Controllers\TestController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AccessDoorSyncAttCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'access_door_sync_attendance:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Performs attendance synchronization with the access door system';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info(app("App\Http\Controllers\TestController")->new_get_attendances());
        Log::info("SUCCESS GET DATA ATTENDANCE");
        return Command::SUCCESS;
    }
}
