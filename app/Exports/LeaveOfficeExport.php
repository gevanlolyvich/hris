<?php

namespace App\Exports;

use App\Models\LeaveOffice;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveType;
use App\Models\ShiftTime;
use App\Models\Utility;
use App\Utilities\DistanceCalculator;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeaveOfficeExport implements FromCollection, WithEvents, ShouldAutoSize, WithTitle, WithCustomStartCell
{
    private $query;
    private $total_data;

    // Modify the constructor to accept parameters
    public function __construct($query)
    {
        $this->query            = json_decode($query);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data       = collect();

        $time = '';
        // Inserting Legend Data
        if (empty($this->query?->type) || $this->query?->type == 'monthly') {
            $currentdate = strtotime($this->query?->month);
            $month       = date('m', $currentdate);
            $year        = date('Y', $currentdate);
            $time        = $this->query?->month;
        } else {
            $month    = date('m');
            $year     = date('Y');
            $time     = $this->query?->date;
        }

        $subTitle   =  __('Leave Office Permit') . ' ';

        $settings   = Utility::settings();

        $data->push([$settings['company_name'], '' , '' , '' , '', __('')]);
        $data->push([ "{$subTitle}  {$time}", '' , '' , '' , '' , __('')]);
        $data->push(['', __('')]);
        $data->push(['No', __('Date'), __('Name'), __('Designation'), __('Branch'), __('Department'), __('Location'), __('Need'), __('Description'), __('Status'), __('Leave Office Time'), __('Return Office'), '', '' ]);
        $data->push(['', '', '', '', '', '', '', '', '', '', '', __('Time'), __('Picture'), __('Location')]);
        
        // Loading Real Data
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

        $branch = $branch_id?->isNotEmpty() ? Branch::whereIn('id', $branch_id)->get()->pluck('name', 'id') : Branch::get()->pluck('name', 'id');

        $leaves       = LeaveOffice::orderby('date', 'ASC');

        // Filter result by user, superior can see it's subordinate data
        if(\Auth::user()->type == 'employee') {
            $emp = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;

            $userId = \Auth::user()->employee->user_id;
            $subordinates = \Auth::user()->employee->subordinatesFlatten();

            // Check if employee managing other employee or not
            if ($subordinates->isNotEmpty()) {
                $employees = collect();
                foreach ($subordinates as $subordinate) {
                    $employees->push($subordinate->id);
                }

                $employees->push($emp);

                $leaves = $leaves->whereIn('employee_id', $employees);
            } else {
                $leaves = $leaves->where('employee_id', $emp);
            }
        }
        else
        {
            $employee = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->select('id') : Employee::select('id');
            if (!empty($this->query?->branch)) {
                $employee->where('branch_id', $this->query?->branch);
            }

            $employee = $employee?->orderby('name', 'asc')?->get()?->pluck('id');

            $leaves = $leaves->whereIn('employee_id', $employee);
        }

        // Filter by optional query
        if ($this->query?->type == 'monthly' && !empty($this->query?->month)) {
            $month = date('m', strtotime($this->query?->month));
            $year  = date('Y', strtotime($this->query?->month));

            $start_date = date($year . '-' . $month . '-01');
            $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

            $leaves->whereBetween(
                'date',
                [
                    $start_date,
                    $end_date,
                ]
            );
        } elseif ($this->query?->type == 'daily' && !empty($this->query?->date)) {
            $leaves->where('date', $this->query?->date);
        } else {
            $month      = date('m');
            $year       = date('Y');
            $start_date = date($year . '-' . $month . '-01');
            $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

            $leaves->whereBetween(
                'date',
                [
                    $start_date,
                    $end_date,
                ]
            );
        }
        $leaves   = $leaves->get();

        $this->total_data = count($leaves) + 6;

        foreach($leaves as $index => $leave) {
            $data->push([
                $index + 1,
                $leave->date,
                $leave->employee->name,
                $leave->employee?->designation?->name,
                $leave->employee?->branch?->name,
                $leave->employee?->department?->name,
                $leave->location,
                $leave->need,
                $leave->description,
                __($leave->status),
                $leave->leave,
                $leave->return,
                !empty($leave->return_coord) ? "https://www.google.co.id/maps/search/" . implode(',', array_slice(explode(', ', $leave->return_coord), 0, -1)) : '-',
                $leave->return_pict,
            ]);
        }

        return $data;
    }

    public function startCell(): string
    {
        return 'B2';
    }

    public function title(): string
    {
        return __('Leave Office Permit');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Merged Cells
                $sheet->mergeCells('B2:F2');
                $sheet->mergeCells('B3:F3');
                $sheet->mergeCells('B5:B6');
                $sheet->mergeCells('C5:C6');
                $sheet->mergeCells('C5:C6');
                $sheet->mergeCells('C5:C6');
                $sheet->mergeCells('D5:D6');
                $sheet->mergeCells('E5:E6');
                $sheet->mergeCells('F5:F6');
                $sheet->mergeCells('H5:H6');
                $sheet->mergeCells('I5:I6');
                $sheet->mergeCells('G5:G6');
                $sheet->mergeCells('J5:J6');
                $sheet->mergeCells('K5:K6');
                $sheet->mergeCells('L5:L6');
                $sheet->mergeCells('M5:O5');

                // Apply Header Style
                $this->applyHeaderCellStyles($sheet, 'B5:B6');
                $this->applyHeaderCellStyles($sheet, 'C5:C6');
                $this->applyHeaderCellStyles($sheet, 'D5:D6');
                $this->applyHeaderCellStyles($sheet, 'E5:E6');
                $this->applyHeaderCellStyles($sheet, 'F5:F6');
                $this->applyHeaderCellStyles($sheet, 'G5:G6');
                $this->applyHeaderCellStyles($sheet, 'H5:H6');
                $this->applyHeaderCellStyles($sheet, 'I5:I6');
                $this->applyHeaderCellStyles($sheet, 'J5:J6');
                $this->applyHeaderCellStyles($sheet, 'K5:K6');
                $this->applyHeaderCellStyles($sheet, 'L5:L6');
                $this->applyHeaderCellStyles($sheet, 'M5:O5');
                $this->applyHeaderCellStyles($sheet, 'M6');
                $this->applyHeaderCellStyles($sheet, 'N6');
                $this->applyHeaderCellStyles($sheet, 'O6');

                
                // Style Cells
                $sheet->getStyle('B2:B3')->applyFromArray(['font' => ['bold' => true]]);
                $sheet->getColumnDimension('J')->setAutoSize(false);
                $sheet->getColumnDimension('N')->setAutoSize(false);
                $sheet->getColumnDimension('O')->setAutoSize(false);
                $sheet->getStyle("L7:L{$this->total_data}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E0EFFC'],
                    ],
                ]);
                $sheet->getStyle("M7:M{$this->total_data}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E0EFFC'],
                    ],
                ]);

                // Freeze Cells
                $sheet->freezePane('E7');

                // make border for all data
                $sheet->getStyle("B7:O{$this->total_data}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);
                $sheet->getStyle("L7:M{$this->total_data}")->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ]
                ]);
            },
        ];
    }

    protected function applyHeaderCellStyles($sheet, $targetCells = null)
    {
        $styleArray = [
            'font' => ['bold' => true],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                ]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ]
        ];

        if ($targetCells) {
            $sheet->getStyle($targetCells)->applyFromArray($styleArray);
        } else {
            $sheet->getStyle($styleArray);
        }
    }


    protected function generateColumnNames($totalDays) {
        $columnNames = [];
        $currentColumn = 'G';
    
        for ($i = 0; $i < $totalDays; $i++) {
            $columnNames[] = $currentColumn;
    
            // Increment the column name
            $currentColumn++;
            
            // If the current column exceeds 'Z', reset to 'A' and append another letter to form 'AA', 'AB', etc.
            if ($currentColumn > 'Z') {
                $currentColumn = 'A' . $currentColumn;
            }
        }
    
        return $columnNames;
    }
}
