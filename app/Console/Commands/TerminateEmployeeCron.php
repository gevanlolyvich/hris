<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\User;
use App\Models\Termination;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TerminateEmployeeCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'terminate:employees';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Terminate employees based on termination dates';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = now()->toDateString();

        // Terminate employees with today as the termination date
        $terminations = Termination::where('termination_date', $today)->select('employee_id', 'termination_date')->get()->pluck('employee_id');
        $old_terminations = Termination::where('termination_date', '<', $today)->select('employee_id', 'termination_date')->get()->pluck('employee_id');
        $cancel_terminations = Termination::where('termination_date', '>', $today)->select('employee_id', 'termination_date')->get()->pluck('employee_id');

        $users = Employee::whereIn('id', $terminations)
            ->where('is_active', 1)
            ->select('user_id')->get()->pluck('user_id');
        Employee::whereIn('id', $terminations)
            ->where('is_active', 1)
            ->update(['is_active' => 0, 'termination_date' => date('Y-m-d')]);
        User::whereIn('id', $users)
            ->where('is_active', 1)
            ->update(['is_active' => 0]);


        // Terminate employee that have passing termination date
        $old_users = Employee::whereIn('id', $old_terminations)
            ->where('is_active', 1)
            ->select('user_id')->get()->pluck('user_id');
        foreach ($old_terminations as $old_id) {
            $termination = Termination::where('employee_id', $old_id)->select('employee_id', 'termination_date')->first();
            Employee::where('id', $old_id)
                ->where('is_active', 1)
                ->update(['is_active' => 0, 'termination_date' => $termination->termination_date]);
        }
        User::whereIn('id', $old_users)
            ->where('is_active', 1)
            ->update(['is_active' => 0]);


        // Cancel termination when termination date of employee changes
        $cancel_users = Employee::whereIn('id', $cancel_terminations)
            ->where('is_active', 0)
            ->select('user_id')->get()->pluck('user_id');
        Employee::whereIn('id', $cancel_terminations)
            ->where('is_active', 0)
            ->update(['is_active' => 1, 'termination_date' => null]);
        User::whereIn('id', $cancel_users)
            ->where('is_active', 0)
            ->update(['is_active' => 1]);

        $this->info('Employee termination process completed.');
        return Command::SUCCESS;
    }
}
