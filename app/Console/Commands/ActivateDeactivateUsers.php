<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\EmployeePeriod;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActivateDeactivateUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:activate-deactivate-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate or deactivate users based on their employment periods, considering start and end dates.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString();
        $tomorrow = Carbon::tomorrow()->toDateString(); // Get tomorrow's date
        $yesterday = Carbon::yesterday()->toDateString();

        // Activate users with a start_period of today
        $activePeriods = EmployeePeriod::where('start_period', $today)
            ->where('active', false)
            ->where('status', 'Approved')
            ->get();

        foreach ($activePeriods as $period) {
            DB::beginTransaction(); // Start transaction for each update

            try {
                $period->active = true;
                $period->sync = true;
                $period->save();

                // Activate employee
                $employee = Employee::findOrFail($period->employee_id);
                $employee->update(['is_active' => true]);

                // Activate user
                $user = User::findOrFail($employee->user_id);
                $user->update(['is_active' => true]);

                Log::info("ID Application = {$period->id} User  Name {$user->name} activated");
                $this->info("ID Application = {$period->id} User  Name {$user->name} activated.");

                DB::commit(); // Commit transaction if successful
            } catch (\Exception $e) {
                DB::rollBack(); // Rollback transaction if error occurs
                Log::error("Error activating user {$period->employee_id}: {$e->getMessage()}");
                $this->error("Error activating user {$period->employee_id}. Please check the logs.");
            }
        }

        // Deactivate users with an end_period of yesterday
        $inactivePeriods = EmployeePeriod::where('end_period', $yesterday)
            ->where('active', true)
            ->where('status', 'Approved')
            ->get();

        foreach ($inactivePeriods as $period) {
            DB::beginTransaction(); // Start transaction for each update

            try {
                // Check if there is a period extension that starts today
                $nextPeriod = EmployeePeriod::where('employee_id', $period->employee_id)
                    ->where('start_period', $today)
                    ->where('status', 'Approved')
                    ->first();

                if ($nextPeriod) {
                    // If there is a period extension, do not deactivate the user
                    Log::info("ID Application = {$period->id} User  Name {$period->employee->name} tidak dinonaktifkan karena ada perpanjangan periode.");
                    $this->info("ID Application = {$period->id} User  Name {$period->employee->name} tidak dinonaktifkan karena ada perpanjangan periode.");
                } else {
                    $period->active = false;
                    $period->save();

                    // Deactivate employee
                    $employee = Employee::findOrFail($period->employee_id);
                    $employee->update(['is_active' => false]);

                    // Deactivate user
                    $user = User::findOrFail($employee->user_id);
                    $user->update(['is_active' => false]);

                    Log::info("ID Application = {$period->id} User  Name {$period->employee->name} deactivated.");
                    $this->info("ID Application = {$period->id} User  Name {$user->name} deactivated.");
                }

                DB::commit(); // Commit transaction if successful
            } catch (\Exception $e) {
                DB::rollBack(); // Rollback transaction if error occurs
                Log::error("Error deactivating user {$period->employee_id}: {$e->getMessage()}");
                $this->error("Error deactivating user {$period->employee_id}. Please check the logs.");
            }
        }
    }
}
