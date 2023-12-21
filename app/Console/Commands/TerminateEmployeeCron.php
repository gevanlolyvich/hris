<?php

namespace App\Console\Commands;

use App\Models\Employee;
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
        Employee::whereIn('id', $terminations)
            ->where('is_active', 1) // Only terminate active employees
            ->update(['is_active' => 0]);

        // Terminate employee that have passing termination date
        Employee::whereIn('id', $old_terminations)
            ->where('is_active', 1) // Only cancel for active employees
            ->update(['is_active' => 0]);

        // Cancel termination when termination date of employee changes
        Employee::whereIn('id', $cancel_terminations)
            ->where('is_active', 0) // Only terminate active employees
            ->update(['is_active' => 1]);

        $this->info('Employee termination process completed.');
        return Command::SUCCESS;
    }
}
