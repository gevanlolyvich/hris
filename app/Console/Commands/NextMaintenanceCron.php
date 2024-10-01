<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vehicle;
use App\Models\VehicleOfficer;
use App\Models\VehicleMaintenance;
use Illuminate\Support\Facades\Log;

class NextMaintenanceCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vehicle:nextMaintenance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification to vehicle officer for next vehicle maintenance date';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = now()->toDateString();

        $maintenances = VehicleMaintenance::where('next_date', $today)->get();

        foreach($maintenances as $maintenance) {
            // Send Notification To Vehicle Officers
            // 1. Collect the reciever (subs) data that we need to send
            $officers = VehicleOfficer::where('is_resricted', 0)
                ->orWhereHas('accesses', function ($query) use ($maintenance) {
                    $query->where('branch_id', $maintenance->vehicle->branch_id);
                })
                ->get();
            $subscriptions = [];
            foreach ($officers as $officer) {
                foreach ($officer?->user?->pushNotifications ?? [] as $sub) {
                    array_push($subscriptions, ['data' => $sub->data, 'name' => $officer->user->name]);
                }
            }

            // 2. Send push notification to list of reciever (subs)
            \Auth::user()->sendNotifications(
                $subscriptions,
                json_encode([
                    'title' => __('Vehicle Maintenance'),
                    'body' => __('Vehicle') . ' '. __('With Name') . ' ' . $maintenance->vehicle->name . ' ' . __('And') . __('Police No') . ' ' . $maintenance->vehicle->police_no. ' ' . __('Need To Maintenance Today'),
                    'url' => "/vehicle-maintenance?type=daily&month=&date={$maintenance->start_date}&vehicle={$maintenance->vehicle->id}"
                ]),
                'normal'
            );
        }

        $this->info('Next maintenance date cron process completed.');
        return Command::SUCCESS;
    }
}
