<?php

namespace App\Exports;

use App\Models\AttendanceEmployee;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveType;
use App\Models\ShiftTime;
use App\Models\Utility;
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

class MonthlyAttendanceExport implements FromCollection, WithEvents, ShouldAutoSize, WithTitle, WithCustomStartCell
{
    private $query;
    private $total_days;
    private $total_data;
    private $date_colomns;
    private $yellowed_cell;
    private $late_cell;

    // Modify the constructor to accept parameters
    public function __construct($query)
    {
        $this->query            = json_decode($query);
        $this->yellowed_cell    = collect();
        $this->late_cell        = collect();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data       = collect();
        
        // Inserting Legend Data
        if (!empty($this->query->month)) {
            $currentdate = strtotime($this->query->month);
            $month       = date('m', $currentdate);
            $year        = date('Y', $currentdate);
        } else {
            $month    = date('m');
            $year     = date('Y');
        }

        $tab_array = ['', '', '', '', ''];

        $subTitle   =  __('Employee Attendance') . ' ';
        $date       = !empty($this->query) ? date('F Y', strtotime($this->query->month)) : date('F Y');

        $num_of_days = date('t', mktime(0, 0, 0, $month, 1, $year));
        $this->total_days   = $num_of_days;
        $this->date_colomns = $this->generateColumnNames($this->total_days);

        $holiday_date = [];
        for ($i = 1; $i <= $num_of_days; $i++) {
            $formatted_date         = str_pad($i, 2, '0', STR_PAD_LEFT);
            $dates[]                = $formatted_date;
            $date                   = "{$year}-{$month}-{$formatted_date}";
            $holiday                = Holiday::where('start_date', '>=', $date)->where('end_date', '<=', $date)->exists(); 
            $holiday_date[$date]    = $holiday; 
        }

        $settings   = Utility::settings();

        $data->push([$settings['company_name'], '' , '' , '' , '' , __('Out Side Attendance')]);
        $data->push([ "{$subTitle}  {$date}", '' , '' , '' , '' , __('Late')]);
        $data->push(['']);
        $data->push(['No', __('Name'), __('Designation'), __('Branch'), __('Employee Type'), __('Date')]);
        $data->push(array_merge($tab_array, $dates));
        
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

        $employees = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->orderBy('name', 'ASC') : Employee::orderBy('name', 'ASC');
        if (!empty($this->query->branch)) {
            // $employees->where('branch_id', $request->branch);
            $employees      = $employees->where('branch_id', $this->query->branch);
        }

        if (!empty($this->query->department)) {
            // $employees->where('department_id', $request->department);
            $employees          = $employees->where('department_id', $this->query->department);
        }

        $employees = $employees->get();

        $this->total_data = count($employees) + 6;

        foreach ($employees as $index => $employee) {
            $employeeArray          = [$index + 1, $employee->name, $employee?->designation?->name ?? '-', $employee?->branch?->name ?? '-', $employee?->employeeType?->name ?? '-'];
            $employee_attendances   = AttendanceEmployee::where('employee_id', $employee->id)->whereMonth('date', $month)->whereYear('date', $year)->select('date', 'clock_in', 'status', 'early_leaving', 'late', 'attendance_type_id', 'is_valid', 'shift_type_id')->get();

            $shift                  = ShiftTime::where('shift_type_id', $employee->shift_type->id)->select('is_working', 'days')->get()->pluck('is_working', 'days');
            $totalAttendance        = 0;
            $arrayAttendanceDate    = [];
            $totalLate              = 0;
            
            foreach ($dates as $d => $date) {
                $dateFormat = $year . '-' . $month . '-' . $date;

                if ($dateFormat <= date('Y-m-d')) {
                    $attendances_on_date    = $employee_attendances->where('date', $dateFormat);
                    $present                = false;
                    $leave                  = false;
                    $permission             = false;
                    $date_data              = '';
                    $day                    = date('l', strtotime($dateFormat));

                    if (sizeof($attendances_on_date) > 0) {
                        foreach ($attendances_on_date as $attendance) {
                            $attendance_shift   = $attendance->shift_type->shiftTimes->where('days', $day)->values()[0];
                            if ($attendance->status == 'Present') {
                                $date_data          .= "{$attendance->clock_in}; ";
                                $totalAttendance    += 1;
                                $present            = true;

                                if ($attendance->is_valid && $attendance->attendance_type_id != '1') {
                                    $this->yellowed_cell->push($this->getColomnByDateAndEmployeeIndex($d, $index));
                                }

                                if ($attendance_shift->is_working &&
                                    (strtotime($attendance->clock_in) > (strtotime($attendance_shift->start_time) + ((int)$settings['late_tolerance'] * 60)))) {
                                    $totalLate += 1;
                                    $this->late_cell->push($this->getColomnByDateAndEmployeeIndex($d, $index));
                                }
                            } else if ($attendance->status == 'Leave' && !$present) {
                                $date_data          = __('Leave');
                                $totalAttendance    += 1;
                                $leave              = true;
                            } else if ($attendance->status == 'Permission' && !$present) {
                                $date_data          = __('Permission');
                                $totalAttendance    += 1;
                                $permission         = true;
                            } else if (($holiday_date[$dateFormat] || !$attendance_shift->is_working) && !$leave && !$permission) {
                                $date_data          = __('Holiday');
                            } else {
                                $date_data          = '';
                            }
                        }

                        $arrayAttendanceDate[]  = $date_data;
                    } else if (($holiday_date[$dateFormat] || !$shift[date('l', strtotime($dateFormat))]) && !$leave && !$permission) {
                        $arrayAttendanceDate[]  = __('Holiday');
                    } else {
                        $arrayAttendanceDate[]  = '';
                    }
                } else {
                    $arrayAttendanceDate[]      = '';
                }
            }

            array_push($arrayAttendanceDate, $totalAttendance >= 1 ? $totalAttendance : '0', $totalLate >= 1 ? $totalLate : '0');

            $data->push(array_merge($employeeArray, $arrayAttendanceDate));
        }

        return $data;
    }

    public function startCell(): string
    {
        return 'B2';
    }

    public function title(): string
    {
        return __('Monthly Attendance');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Merged Cells
                $sheet->mergeCells('B2:F2');
                $sheet->mergeCells('B3:F3');
                $sheet->mergeCells('G2:M2');

                // Setting Relative Colomn
                $relativeColomn = $this->getRelativeColumn();
                $sheet->mergeCells($relativeColomn['dateColomn']);
                $sheet->mergeCells($relativeColomn['totalColomn']);
                $sheet->mergeCells($relativeColomn['lateColomn']);
                $sheet->setCellValue($relativeColomn['totalCell'], "Total");
                $sheet->setCellValue($relativeColomn['lateCell'], __('Total Late'));
                $this->applyHeaderCellStyles($sheet, $relativeColomn['dateColomn']);
                $this->applyHeaderCellStyles($sheet, $relativeColomn['dateNumberColomn']);
                $this->applyHeaderCellStyles($sheet, $relativeColomn['totalColomn']);
                $this->applyHeaderCellStyles($sheet, $relativeColomn['lateColomn']);
                
                // Style Cells
                $sheet->getStyle('B2:B3')->applyFromArray(['font' => ['bold' => true]]);
                $sheet->getStyle('G2:M2')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D7F009'],
                    ],
                ]);
                $sheet->getStyle('G3:M3')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEECD8'],
                    ],
                ]);
                
                // Style Columns
                $columns = ['B', 'C', 'D', 'E', 'F'];
                foreach ($columns as $column) {
                    $targetCells = $column . '5:' . $column . '6';
                    $sheet->mergeCells($targetCells);
                    $this->applyHeaderCellStyles($sheet, $targetCells);
                }

                // Freeze Cells
                $sheet->freezePane($relativeColomn['lastCell']);
                $sheet->freezePane('D7');

                // make border for all data
                
                $sheet->getStyle("B7:{$relativeColomn['lastColomn']}{$this->total_data}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ],
                ]);

                // Search For Holiday To Style It
                foreach ($sheet->getRowIterator(7) as $row) {
                    // Check if 'late' is not '00:00:00'
                    foreach ($this->date_colomns as $day => $colomn) {
                        if ($day + 1 <= (int)date('d')) {
                            $cellValue = $sheet->getCell($colomn . $row->getRowIndex())->getValue();
        
                            if ($cellValue == __('Holiday')) {
                                $sheet->getStyle($colomn . $row->getRowIndex())->applyFromArray([
                                    'fill' => [
                                        'fillType' => Fill::FILL_SOLID,
                                        'startColor' => ['rgb' => 'DDEBF7'],
                                    ],
                                ]);
                            }
                        }
                    }
                }

                // Marking cell that attendance outside of workplace
                foreach ($this->yellowed_cell as $cell) {
                    $sheet->getStyle($cell)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'D7F009'],
                        ],
                    ]);
                }

                // Marking cell that attendance is late
                foreach ($this->late_cell as $cell) {
                    $sheet->getStyle($cell)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FEECD8'],
                        ],
                    ]);
                }
            },
        ];
    }

    protected function getRelativeColumn(): array
    {
        $lastDateColumn = 'AI';
        $totalColumn = 'AJ';
        $lateColumn = 'AK';

        switch ($this->total_days) {
            case 28:
                $lastDateColumn = 'AH';
                $totalColumn = 'AI';
                $lateColumn = 'AJ';
                break;
            case 29:
                $lastDateColumn = 'AI';
                $totalColumn = 'AJ';
                $lateColumn = 'AK';
                break;
            case 30:
                $lastDateColumn = 'AJ';
                $totalColumn = 'AK';
                $lateColumn = 'AL';
                break;
            case 31:
                $lastDateColumn = 'AK';
                $totalColumn = 'AL';
                $lateColumn = 'AM';
                break;
        }
        return [
            'dateColomn' => "G5:{$lastDateColumn}5",
            'dateNumberColomn' => "G6:{$lastDateColumn}6",
            'totalColomn' => "{$totalColumn}5:{$totalColumn}6",
            'totalCell' => "{$totalColumn}5",
            'lastColomn' => $lateColumn,
            'lateColomn' => "{$lateColumn}5:{$lateColumn}6",
            'lateCell' => "{$lateColumn}5",
            'lastCell' => "{$lateColumn}6",
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

    protected function getColomnByDateAndEmployeeIndex($date, $index) : STRING {
        $colomn     = $this->date_colomns[$date] . $index + 7;
        return $colomn;
    }
}
