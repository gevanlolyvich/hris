<?php

namespace App\Exports;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\AttendanceEmployee;
use App\Models\LeaveType;
use App\Utilities\AttendanceLocationResolver;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithTitle;

class IndividualMarkedAttendanceExport implements FromCollection, WithHeadings, WithEvents, ShouldAutoSize, WithTitle
{
    private $query;

    // Modify the constructor to accept parameters
    public function __construct($query)
    {
        $this->query = $query;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data           = collect();
        $attendances    = AttendanceEmployee::where('employee_id', $this?->query?->employee_id);

        if (property_exists($this->query, 'type')) {
            if ($this->query?->type == 'monthly' && !empty($this->query?->month)) {
                $month = date('m', strtotime($this->query->month));
                $year  = date('Y', strtotime($this->query->month));
    
                $start_date = date($year . '-' . $month . '-01');
                $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
    
                $attendances->whereBetween(
                    'date',
                    [
                        $start_date,
                        $end_date,
                    ]
                );
            } else if ($this->query?->type == 'daily' && !empty($this->query?->date)) {
                $attendances->where('date', $this->query->date);
            }
        } else {
            $month      = date('m');
            $year       = date('Y');
            $start_date = date($year . '-' . $month . '-01');
            $end_date   = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

            $attendances->whereBetween(
                'date',
                [
                    $start_date,
                    $end_date,
                ]
            );
        }
        $attendances = $attendances->orderBy('date', 'DESC')->get();

        foreach($attendances as $attendance)
            {    
                $data->push([
                    $attendance?->employee?->name ?? 'Deleted Employee',
                    $attendance?->employee?->employee_id ? "{$attendance?->employee?->employee_id} " : '-',
                    AttendanceLocationResolver::resolveBranchName($attendance?->employee_id, $attendance->date) ?? '-',
                    !empty(\Auth::user()->getDepartment($attendance?->employee?->department_id)) ? \Auth::user()->getDepartment($attendance->employee->department_id)->name : '-',
                    !empty(\Auth::user()->getDesignation($attendance?->employee?->designation_id)) ? \Auth::user()->getDesignation($attendance->employee->designation_id)->name : '-',
                    $attendance->shift_type?->name ?? $attendance?->employee?->shift_type?->name,
                    $attendance->date,
                    $attendance->status,
                    $attendance->is_valid ? __('Valid Attendance') : __('Invalid Attendance'),
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

    public function title(): string
    {
        return __('Marked Attendance');
    }

    public function headings(): array
    {
        return [
            __("Employee Name"),
            __("Employee ID"),
            __("Branch"),
            __("Department"),
            __("Designation"),
            __("Shift"),
            __("Date"),
            __("Status"),
            __("Is Valid"),
            __("Clock In"),
            __("Clock Out"),
            __("Late"),
            __("Early Leaving"),
            __("Work Hours"),
            __("Clock In Picture URL"),
            __("Clock In Location URL"),
            __("Clock Out Picture URL"),
            __("Clock Out Location URL"),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                $sheet->getStyle('A1:R1')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                        ]
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_GRADIENT_LINEAR,
                        'rotation' => 90,
                        'startColor' => [
                            'argb' => 'B7BCEE',
                        ],
                        'endColor' => [
                            'argb' => 'CCCDDA',
                        ],
                    ],
                ]);

                if ($sheet->getHighestRow() > 1) {
                    foreach ($sheet->getRowIterator(2) as $row) {
                        // Check if 'late' is not '00:00:00'
                        $cellValue = $sheet->getCell('L' . $row->getRowIndex())->getValue();
    
                        if ($cellValue !== '00:00:00') {
                            $sheet->getStyle('L' . $row->getRowIndex())->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'FF0000'],
                                ],
                            ]);
                        }
    
                        // Check if 'early leaving' is not '00:00:00'
                        $earlyCell = $sheet->getCell('M' . $row->getRowIndex())->getValue();
    
                        if ($earlyCell !== '00:00:00') {
                            $sheet->getStyle('M' . $row->getRowIndex())->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'FF0000'],
                                ],
                            ]);
                        }
    
                        // Check if 'work hours' is under '09:00:00'
                        $workHourCell = $sheet->getCell('N' . $row->getRowIndex())->getValue();
    
                        if (strtotime('08:00:00') > strtotime($workHourCell)) {
                            $sheet->getStyle('N' . $row->getRowIndex())->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'FF0000'],
                                ],
                            ]);
                        }
    
                        // Check if attendance is valid
                        $validCell = $sheet->getCell('I' . $row->getRowIndex())->getValue();
    
                        if ($validCell !== __('Valid Attendance')) {
                            $sheet->getStyle('I' . $row->getRowIndex())->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'FF0000'],
                                ],
                            ]);
                        }
                    }
                }
            },
        ];
    }
}
