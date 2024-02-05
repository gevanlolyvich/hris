<?php

namespace App\Exports;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\AttendanceEmployee;
use App\Models\LeaveType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Log;

class NotClockInExport implements FromCollection, WithEvents, ShouldAutoSize
{
    private $date;

    // Modify the constructor to accept parameters
    public function __construct($date)
    {
        $this->date = $date;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = collect();
        $data->push([__('Exported At'), date('Y-m-d H:i:s')]);
        $data->push([__('Data Date'), $this->date]);

        $branch = Branch::find(\Auth::user()->branch_id);
        $branch_id = collect();
        if ($branch) {
            $branch_id->push($branch?->id);
        }

        $children = $branch?->childBranchFlatten();
        if ($children?->isNotEmpty()) {
            foreach ($children as $child) {
                $branch_id->push($child->id);
            }
        }
    
        $notClockIn         = $branch_id?->isNotEmpty() ? AttendanceEmployee::whereHas('employee', function ($query) use ($branch_id) {
            $query->whereIn('branch_id', $branch_id);
        })->where('date', $this->date)->get()->pluck('employee_id') : AttendanceEmployee::where('date', $this->date)->get()->pluck('employee_id');
        $notClockIns    = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->where('is_active', 1)->whereNotIn('id', $notClockIn)->orderBy('name', 'asc')->get() : Employee::where('is_active', 1)->whereNotIn('id', $notClockIn)->orderBy('name', 'asc')->get();

        $data->push([__('Total Not Clock In'), count($notClockIns)]);
        $data->push(['', '']);

        $data->push([__('Employee'), __('Employee ID'), __('Branch'), __('Department'), __('Designation')]);

        foreach ($notClockIns as $employee) {
            $data->push([
                $employee->name,
                $employee->employee_id,
                $employee->branch?->name ?? '-',
                $employee->department?->name ?? '-',
                $employee->designation?->name ?? '-',
            ]);
        }

        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:A3')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);

                $event->sheet->getStyle('A5:E5')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                        ]
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                        'rotation' => 90,
                        'startColor' => [
                            'argb' => 'B7BCEE',
                        ],
                        'endColor' => [
                            'argb' => 'CCCDDA',
                        ],
                    ],
                ]);
                // $sheet = $event->sheet;

                // foreach ($sheet->getRowIterator(2) as $row) {
                //     // Check if 'late' is not '00:00:00'
                //     $cellValue = $sheet->getCell('L' . $row->getRowIndex())->getValue();

                //     if ($cellValue !== '00:00:00') {
                //         $sheet->getStyle('L' . $row->getRowIndex())->applyFromArray([
                //             'fill' => [
                //                 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                //                 'startColor' => ['rgb' => 'FF0000'],
                //             ],
                //         ]);
                //     }

                //     // Check if 'early leaving' is not '00:00:00'
                //     $earlyCell = $sheet->getCell('M' . $row->getRowIndex())->getValue();

                //     if ($earlyCell !== '00:00:00') {
                //         $sheet->getStyle('M' . $row->getRowIndex())->applyFromArray([
                //             'fill' => [
                //                 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                //                 'startColor' => ['rgb' => 'FF0000'],
                //             ],
                //         ]);
                //     }

                //     // Check if 'work hours' is under '09:00:00'
                //     $workHourCell = $sheet->getCell('N' . $row->getRowIndex())->getValue();

                //     if (strtotime('08:00:00') > strtotime($workHourCell)) {
                //         $sheet->getStyle('N' . $row->getRowIndex())->applyFromArray([
                //             'fill' => [
                //                 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                //                 'startColor' => ['rgb' => 'FF0000'],
                //             ],
                //         ]);
                //     }

                //     // Check if attendance is valid
                //     $validCell = $sheet->getCell('I' . $row->getRowIndex())->getValue();

                //     if ($validCell !== __('Valid Attendance')) {
                //         $sheet->getStyle('I' . $row->getRowIndex())->applyFromArray([
                //             'fill' => [
                //                 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                //                 'startColor' => ['rgb' => 'FF0000'],
                //             ],
                //         ]);
                //     }
                // }
            },
        ];
    }
}
