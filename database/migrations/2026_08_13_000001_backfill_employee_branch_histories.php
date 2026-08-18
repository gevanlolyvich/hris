<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Reconstruct historical branch placements for every employee.
     *
     * - Initial placement: branch = current employees.branch_id, effective from
     *   the employee's earliest attendance date (fallback company_doj / created_at).
     * - One row per transfer: branch = transfer.branch_id, effective from transfer_date.
     *
     * NOTE: The initial branch is taken from the employee's CURRENT branch_id.
     * For employees who transferred before this migration ran, the original
     * branch is not stored anywhere and cannot be reconstructed, so dates
     * before their first recorded transfer will resolve to the current branch.
     * This is a known data limitation; only new placements recorded from now on
     * (EmployeeController, TransferController, TransferEmployeeCron) are exact.
     *
     * Processed in chunks so it is safe on large datasets.
     */
    public function up(): void
    {
        DB::table('employees')->select('id')->orderBy('id')->chunkById(500, function ($employees) {
            foreach ($employees as $employee) {
                $earliestAttendance = DB::table('attendance_employees')
                    ->where('employee_id', $employee->id)
                    ->min('date');

                $employeeRow = DB::table('employees')->where('id', $employee->id)->first(['branch_id', 'company_doj', 'created_at']);
                if (!$employeeRow) {
                    continue;
                }

                $earliestDate = $earliestAttendance;
                if (empty($earliestDate)) {
                    $earliestDate = $employeeRow->company_doj ?? $employeeRow->created_at;
                }
                if (empty($earliestDate)) {
                    $earliestDate = now()->toDateString();
                } else {
                    $earliestDate = date('Y-m-d', strtotime($earliestDate));
                }

                $transfers = DB::table('transfers')
                    ->where('employee_id', $employee->id)
                    ->orderBy('transfer_date', 'asc')
                    ->get(['id', 'branch_id', 'transfer_date']);

                $existingCount = DB::table('employee_branch_histories')
                    ->where('employee_id', $employee->id)
                    ->count();

                if ($existingCount > 0) {
                    continue;
                }

                $rows = [];

                $initialBranch = $employeeRow->branch_id;
                $firstTransfer = $transfers->first();
                $skipInitial = $firstTransfer
                    && (int) $firstTransfer->branch_id === (int) $initialBranch;

                if (!$skipInitial && !empty($initialBranch)) {
                    $rows[] = [
                        'employee_id'    => $employee->id,
                        'branch_id'      => $initialBranch,
                        'effective_date' => $earliestDate,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ];
                }

                foreach ($transfers as $transfer) {
                    $rows[] = [
                        'employee_id'    => $employee->id,
                        'branch_id'      => $transfer->branch_id,
                        'effective_date' => $transfer->transfer_date,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ];
                }

                if (!empty($rows)) {
                    DB::table('employee_branch_histories')->insert($rows);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('employee_branch_histories')->truncate();
    }
};