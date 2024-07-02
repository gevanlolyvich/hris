<?php

namespace App\Exports;

use App\Models\Branch;
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
use Maatwebsite\Excel\Concerns\WithTitle;

class IndividualAttendanceSummaryExport implements FromCollection, WithHeadings, WithEvents, ShouldAutoSize, WithTitle
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
        $employees      = Employee::where('id', $this->query->employee_id);
        $validCount     = null;
        $invalidCount   = null;
        
        // Getting Sum Of Attendance
        if (property_exists($this->query, 'type')) {
            if ($this->query->type == 'monthly' && !empty($this->query->month)) {
                $month = date('m', strtotime($this->query->month));
                $year  = date('Y', strtotime($this->query->month));
    
                $start_date = date($year . '-' . $month . '-01');
                $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                $employees->whereHas('attendances')->withCount([
                    'attendances as total_attendance' => function ($q) use ($start_date, $end_date) {
                        $q->whereBetween('date', [$start_date, $end_date]);
                    },
                    'attendances as total_valid_attendance' => function ($q) use ($start_date, $end_date) {
                        $q->whereBetween('date', [$start_date, $end_date])->where('is_valid', 1);
                    },
                    'attendances as total_invalid_attendance' => function ($q) use ($start_date, $end_date) {
                        $q->whereBetween('date', [$start_date, $end_date])->whereNull('is_valid');
                    },
                ]);
            } else if ($this->query->type == 'daily' && !empty($this->query->date)) {
                $employees->whereHas('attendances')->withCount([
                    'attendances as total_attendance' => function ($q) {
                        $q->where('date', $this->query->date);
                    },
                    'attendances as total_valid_attendance' => function ($q) {
                        $q->where('date', $this->query->date)->where('is_valid', 1);
                    },
                    'attendances as total_invalid_attendance' => function ($q) {
                        $q->where('date', $this->query->date)->whereNull('is_valid');
                    },
                ]);
                // $employees->where('date', $this->query->date);
            }
        }  else  {
            $month = date('m');
            $year  = date('Y');
            $start_date = date($year . '-' . $month . '-01');
            $end_date = date('Y-m-t');

            $employees->whereHas('attendances')->withCount([
                'attendances as total_attendance' => function ($q) use ($start_date, $end_date) {
                    $q->whereBetween('date', [$start_date, $end_date]);
                },
                'attendances as total_valid_attendance' => function ($q) use ($start_date, $end_date) {
                    $q->whereBetween('date', [$start_date, $end_date])->where('is_valid', 1);
                },
                'attendances as total_invalid_attendance' => function ($q) use ($start_date, $end_date) {
                    $q->whereBetween('date', [$start_date, $end_date])->whereNull('is_valid');
                },
            ]);
        }

        $employees = $employees->get();

        foreach($employees as $employee)
            {    
                $data->push([
                    $employee->name,
                    $employee->total_attendance >= 1 ? $employee->total_attendance : "0",
                    $employee->total_valid_attendance >= 1 ? $employee->total_valid_attendance : "0",
                    $employee->total_invalid_attendance >= 1 ? $employee->total_invalid_attendance : "0",
                ]);
            }

        return $data;
    }

    public function title(): string
    {
        return __('Attendance Summary');
    }

    public function headings(): array
    {
        return [
            __("Employee Name"),
            __('Total Attendance'),
            __('Total Valid Attendance'),
            __('Total Invalid Attendance')
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                $sheet->getStyle('A1:D1')->applyFromArray([
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
                        $cellValue = $sheet->getCell('D' . $row->getRowIndex())->getValue();
    
                        if ($cellValue >= 1) {
                            $sheet->getStyle('D' . $row->getRowIndex())->applyFromArray([
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
