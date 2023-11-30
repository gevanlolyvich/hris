<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\AttendanceEmployee;
use App\Models\LeaveType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Log;

class AttendanceExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = collect();
        if (\Auth::user()->type == 'employee')
        {
            $subordinate_ids = \Auth::user()?->employee?->subordinatesFlatten()->pluck('id')->toArray();
            $employee_id = null;
            if (!empty($subordinate_ids))
            {
                $employee_id = $subordinate_ids;
                $employee_id[] = \Auth::user()->employee->id;
            }
            else 
            {
                $employee_id[] = \Auth::user()->employee->id;
            }
          
            $attendances= AttendanceEmployee::whereIn('employee_id', $employee_id)->orderBy('date', 'DESC')->orderBy('employee_id', 'ASC')->get();

            foreach($attendances as $attendance)
            {    
                Log::info($attendance);

                $data->push([
                    $attendance->employee->name,
                    !empty(\Auth::user()->getBranch($attendance->employee->branch_id)) ? \Auth::user()->getBranch($attendance->employee->branch_id)->name : '-',
                    !empty(\Auth::user()->getDepartment($attendance->employee->department_id)) ? \Auth::user()->getDepartment($attendance->employee->department_id)->name : '-',
                    !empty(\Auth::user()->getDesignation($attendance->employee->designation_id)) ? \Auth::user()->getDesignation($attendance->employee->designation_id)->name : '-',
                    $attendance->shift_type?->name ?? $attendance->employee->shift_type?->name,
                    $attendance->date,
                    $attendance->status,
                    $attendance->clock_in,
                    $attendance->clock_out,
                    $attendance->late,
                    $attendance->early_leaving,
                    $attendance->overtime,
                    $attendance->work_hours,
                ]);
            }
        } else {
            $attendances = AttendanceEmployee::orderBy('date', 'DESC')->orderBy('employee_id', 'ASC')->get();
            foreach($attendances as $attendance)
            {    
                $data->push([
                  $attendance->employee->name,
                  !empty(\Auth::user()->getBranch($attendance->employee->branch_id)) ? \Auth::user()->getBranch($attendance->employee->branch_id)->name : '-',
                  !empty(\Auth::user()->getDepartment($attendance->employee->department_id)) ? \Auth::user()->getDepartment($attendance->employee->department_id)->name : '-',
                  !empty(\Auth::user()->getDesignation($attendance->employee->designation_id)) ? \Auth::user()->getDesignation($attendance->employee->designation_id)->name : '-',
                  $attendance->shift_type?->name ?? $attendance->employee->shift_type?->name,
                  $attendance->date,
                  $attendance->status,
                  $attendance->clock_in,
                  $attendance->clock_out,
                  $attendance->late,
                  $attendance->early_leaving,
                  $attendance->overtime,
                  $attendance->work_hours,
              ]);
            }
        }
    
        return $data;
    }

    public function headings(): array
    {
        return [
            "Employee Name",
            "Branch",
            "Department",
            "Designation",
            "Shift",
            "Date",
            "Status",
            "Clock In",
            "Clock Out",
            "Late",
            "Early Leaving",
            "Overtime",
            "Work Hours",
        ];
    }
}
