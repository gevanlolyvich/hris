<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;

class TransferEmployeeCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transfer:employees';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer employees based on termination dates';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = now()->toDateString();

        // Transfer employees with today as the transfer date
        $transfers = Transfer::where('transfer_date', $today)->get();
        $old_transfers = Transfer::where('transfer_date', '<', $today)->get();

        for ($i = 0; $i < count($transfers); $i++) {
            $transfer   = $transfers[$i];
            $emp        = Employee::where('id', $transfer->employee_id)->first();

            Employee::where('id', $transfer->employee_id)->update([
                'branch_id'         => $transfer->branch_id,
                'department_id'     => $transfer->department_id,
                'designation_id'    => $transfer->designation_id,
                'managed_by'        => $transfer->managed_by ?? null,
                'shift_type_id'     => $transfer->shift_type_id,
            ]);

            User::where('id', $emp->user_id)->update([
                'branch_id' => $transfer->branch_id,
            ]);
        }

        for ($i = 0; $i < count($old_transfers); $i++) {
            $old_transfer   = $old_transfers[$i];
            $emp            = Employee::where('id', $old_transfer->employee_id)->first();

            Employee::where('id', $old_transfer->employee_id)->update([
                'branch_id'         => $old_transfer->branch_id,
                'department_id'     => $old_transfer->department_id,
                'designation_id'    => $old_transfer->designation_id,
                'managed_by'        => $old_transfer->managed_by ?? null,
                'shift_type_id'     => $old_transfer->shift_type_id,
            ]);

            User::where('id', $emp->user_id)->update([
                'branch_id' => $old_transfer->branch_id,
            ]);
        }

        $this->info('Employee transfer process completed.');
        return Command::SUCCESS;
    }
}
