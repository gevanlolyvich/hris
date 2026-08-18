<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeBranchHistory extends Model
{
    protected $fillable = [
        'employee_id',
        'branch_id',
        'effective_date',
    ];

    /**
     * Record a new branch placement for an employee.
     *
     * @param  int     $employeeId
     * @param  int     $branchId
     * @param  string  $effectiveDate  Y-m-d
     * @return self
     */
    public static function record($employeeId, $branchId, $effectiveDate)
    {
        return static::create([
            'employee_id'    => $employeeId,
            'branch_id'      => $branchId,
            'effective_date' => date('Y-m-d', strtotime($effectiveDate)),
        ]);
    }

    /**
     * Ensure an employee has an initial placement history row.
     *
     * Employees created before this feature, or through flows that do not
     * record a placement, may have no history at all. Without an initial row
     * the AttendanceLocationResolver falls back to employees.branch_id, which
     * is the *current* branch and becomes wrong once the employee transfers.
     *
     * When no history exists yet, record the employee's current branch as the
     * initial placement, effective from the earliest attendance (fallback
     * company_doj, created_at).
     *
     * @param  int  $employeeId
     * @return self|null
     */
    public static function ensureInitialPlacement($employeeId)
    {
        if (static::where('employee_id', $employeeId)->exists()) {
            return null;
        }

        $employee = Employee::where('id', $employeeId)->first(['id', 'branch_id', 'company_doj', 'created_at']);
        if (!$employee || empty($employee->branch_id)) {
            return null;
        }

        $effectiveDate = AttendanceEmployee::where('employee_id', $employeeId)->min('date');
        if (empty($effectiveDate)) {
            $effectiveDate = $employee->company_doj ?? $employee->created_at;
        }
        if (empty($effectiveDate)) {
            $effectiveDate = now()->toDateString();
        }

        return static::record($employeeId, $employee->branch_id, $effectiveDate);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }
}