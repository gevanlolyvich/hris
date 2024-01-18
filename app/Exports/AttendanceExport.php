<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\AttendanceEmployee;
use App\Models\LeaveType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Log;

class AttendanceExport implements FromCollection, WithHeadings, WithEvents, ShouldAutoSize
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
          
            $attendances= AttendanceEmployee::whereIn('employee_id', $employee_id)->orderBy('date', 'DESC');

        } else {
            $employee_id = !empty(\Auth::user()->branch_id) ? Employee::where('branch_id', \Auth::user()?->branch_id)->get()->pluck('id')->toArray() : Employee::get()->pluck('id')->toArray();
            $attendances = !empty(\Auth::user()?->branch_id) ? AttendanceEmployee::whereIn('employee_id', $employee_id)->orderBy('date', 'DESC') : AttendanceEmployee::orderBy('date', 'DESC');
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
            } else if ($query->type == 'daily' && !empty($query->date)) {
                $attendances->where('date', $query->date);
            }
        }  else  {
            $attendances->where('date', date('Y-m-d'));
        }

        $attendances = $attendances->withAggregate('employee', 'name')->orderBy('employee_name', 'asc')->get();

        foreach($attendances as $attendance)
            {    
                $data->push([
                    $attendance?->employee?->name ?? 'Deleted Employee',
                    $attendance?->employee?->employee_id ?? '-',
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
                    $attendance->work_hours ?? '00:00:00' ,
                    (strpos($attendance->picture_in, 'http') != 0) && !empty($attendance->picture_in) ? env('APP_URL') . $attendance->picture_in : $attendance->picture_in ?? '-    ',
                    $attendance->coord_in ? "https://www.google.co.id/maps/search/" . implode(',', array_slice(explode(', ', $attendance->coord_in), 0, -1)) : '-',
                    (strpos($attendance->picture_out, 'http') != 0) && !empty($attendance->picture_out) ? env('APP_URL') . $attendance->picture_out : $attendance->picture_out ?? '-    ',
                    $attendance->coord_out ? "https://www.google.co.id/maps/search/" . implode(',', array_slice(explode(', ', $attendance->coord_out), 0, -1)) : '-',
                ]);
            }

        return $data;
    }

    public function headings(): array
    {
        return [
            "Employee Name",
            "Employee ID",
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
            "Work Hours",
            "Clock In Picture URL",
            "Clock In Location URL",
            "Clock Out Picture URL",
            "Clock Out Location URL",
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                foreach ($sheet->getRowIterator(2) as $row) {
                    $cellValue = $sheet->getCell('K' . $row->getRowIndex())->getValue();

                    // Check if 'late' is not '00:00:00'
                    if ($cellValue !== '00:00:00') {
                        $sheet->getStyle('K' . $row->getRowIndex())->applyFromArray([
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FF0000'],
                            ],
                        ]);
                    }

                    $earlyCell = $sheet->getCell('L' . $row->getRowIndex())->getValue();

                    // Check if 'early leaving' is not '00:00:00'
                    if ($earlyCell !== '00:00:00') {
                        $sheet->getStyle('L' . $row->getRowIndex())->applyFromArray([
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FF0000'],
                            ],
                        ]);
                    }

                    $workHourCell = $sheet->getCell('M' . $row->getRowIndex())->getValue();

                    // Check if 'work hours' is under '09:00:00'
                    if (strtotime('08:00:00') > strtotime($workHourCell)) {
                        $sheet->getStyle('M' . $row->getRowIndex())->applyFromArray([
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FF0000'],
                            ],
                        ]);
                    }
                }
            },
        ];
    }
}
