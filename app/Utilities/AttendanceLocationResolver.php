<?php

namespace App\Utilities;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeBranchHistory;
use App\Models\Transfer;

class AttendanceLocationResolver
{
    /**
     * Cache of per-employee location data (initial branch + transfers),
     * keyed by employee id.
     *
     * @var array<int, array{initial_branch_id: int|null, transfers: \Illuminate\Support\Collection}>
     */
    protected static $historyCache = [];

    /**
     * Resolve the branch id an employee belonged to at a given date.
     *
     * The employee branch placement timeline is:
     * - initial branch: employee_branch_histories (earliest effective_date row)
     * - transfer events: transfers table, sorted by transfer_date ascending
     *
     * Rule:
     * - attendance_date >= transfer_date => use that transfer's branch.
     * - attendance_date < first transfer => initial branch from
     *   employee_branch_histories.
     * - employee without any transfer (or missing initial history) =>
     *   fall back to the employee's current branch.
     *
     * @param  int     $employeeId
     * @param  string  $date  Y-m-d attendance date
     * @return int|null
     */
    public static function resolveBranchId($employeeId, $date)
    {
        $date = date('Y-m-d', strtotime($date));
        $data = self::locationData($employeeId);

        $branchId = null;
        foreach ($data['transfers'] as $transfer) {
            if ($transfer->transfer_date <= $date) {
                $branchId = (int) $transfer->branch_id;
            } else {
                break;
            }
        }

        if ($branchId) {
            return $branchId;
        }

        if (!empty($data['initial_branch_id'])) {
            return (int) $data['initial_branch_id'];
        }

        return Employee::where('id', $employeeId)->value('branch_id');
    }

    /**
     * Resolve the branch name an employee belonged to at a given date.
     *
     * @param  int     $employeeId
     * @param  string  $date  Y-m-d attendance date
     * @return string|null
     */
    public static function resolveBranchName($employeeId, $date)
    {
        $branchId = self::resolveBranchId($employeeId, $date);
        if (empty($branchId)) {
            return null;
        }

        $branch = Branch::find($branchId);

        return $branch?->name;
    }

    /**
     * Load and cache the employee branch placement data.
     *
     * The initial branch reference is the earliest employee_branch_histories
     * row and is immutable (it does not change when employees.branch_id or
     * transfers change). Transfer events come from the transfers table.
     *
     * @param  int  $employeeId
     * @return array{initial_branch_id: int|null, transfers: \Illuminate\Support\Collection}
     */
    protected static function locationData($employeeId)
    {
        if (!isset(static::$historyCache[$employeeId])) {
            $initialBranchId = EmployeeBranchHistory::where('employee_id', $employeeId)
                ->orderBy('effective_date', 'asc')
                ->value('branch_id');

            $transfers = Transfer::where('employee_id', $employeeId)
                ->orderBy('transfer_date', 'asc')
                ->get(['branch_id', 'transfer_date']);

            static::$historyCache[$employeeId] = [
                'initial_branch_id' => $initialBranchId ? (int) $initialBranchId : null,
                'transfers'         => $transfers,
            ];
        }

        return static::$historyCache[$employeeId];
    }

    /**
     * Clear the internal cache. Useful for tests.
     */
    public static function flush()
    {
        static::$historyCache = [];
    }
}
