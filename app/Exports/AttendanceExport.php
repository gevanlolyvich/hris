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
    private $urlParameters;

    // Modify the constructor to accept parameters
    public function __construct($urlParameters)
    {
        $this->urlParameters = $urlParameters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = json_decode($this->urlParameters);
        $data = collect();
        $attendances = null;
        if (\Auth::user()->type == 'employee')
        {
            $subordinate_ids = \Auth::user()?->employee?->subordinatesFlatten()->pluck('id')->toArray();
            $employee_id = null;
            if (!empty($subordinate_ids)) {
                $employee_id = $subordinate_ids;
                $employee_id[] = \Auth::user()->employee->id;
            }
            else {
                $employee_id[] = \Auth::user()->employee->id;
            }
          
            $attendances= AttendanceEmployee::whereIn('employee_id', $employee_id)->orderBy('date', 'DESC')->orderBy('employee_id', 'ASC');

        } else {
            $attendances = AttendanceEmployee::orderBy('date', 'DESC')->orderBy('employee_id', 'ASC');
        }

        if (!empty($query)) {
            if ($query->type == 'monthly' && !empty($query->month)) {
                $month = date('m', strtotime($query->month));
                $year  = date('Y', strtotime($query->month));
    
                $start_date = date($year . '-' . $month . '-01');
                $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
    
                $attendances->whereBetween(
                    'date',
                    [
                        $start_date,
                        $end_date,
                    ]
                );
            } elseif ($query->type == 'daily' && !empty($query->date)) {
                $attendances->where('date', $query->date);
            }
        }  else  {
            $attendances->where('date', date('Y-m-d'));
        }

        $attendances = $attendances->get();
    
        foreach($attendances as $attendance)
            {    
                $data->push([
                    $attendance?->employee?->name ?? 'Deleted Employee',
                    !empty(\Auth::user()->getBranch($attendance?->employee?->branch_id)) ? \Auth::user()->getBranch($attendance->employee->branch_id)->name : '-',
                    !empty(\Auth::user()->getDepartment($attendance?->employee?->department_id)) ? \Auth::user()->getDepartment($attendance->employee->department_id)->name : '-',
                    !empty(\Auth::user()->getDesignation($attendance?->employee?->designation_id)) ? \Auth::user()->getDesignation($attendance->employee->designation_id)->name : '-',
                    $attendance->shift_type?->name ?? $attendance?->employee?->shift_type?->name,
                    $attendance->date,
                    $attendance->status,
                    $attendance->clock_in,
                    $attendance->clock_out,
                    $attendance->late,
                    $attendance->early_leaving,
                    $attendance->overtime,
                    $attendance->work_hours,
                    strpos($attendance->picture_in, 'http') ? $attendance->picture_in : env('APP_URL') . $attendance->picture_in,
                    $attendance->coord_in ? "https://www.google.co.id/maps/search/" . implode(',', array_slice(explode(', ', $attendance->coord_in), 0, -1)) : '-',
                    strpos($attendance->picture_out, 'http') ? $attendance->picture_out : env('APP_URL') . $attendance->picture_out,
                    $attendance->coord_out ? "https://www.google.co.id/maps/search/" . implode(',', array_slice(explode(', ', $attendance->coord_out), 0, -1)) : '-',
                ]);
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
            "Clock In Picture URL",
            "Clock In Location URL",
            "Clock Out Picture URL",
            "Clock Out Location URL",
        ];
    }
}
