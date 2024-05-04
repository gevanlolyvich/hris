<?php

namespace App\Exports;

use App\Models\AttendanceEmployee;
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
            $employee_attendances   = AttendanceEmployee::where('employee_id', $employee->id)->whereMonth('date', $month)->whereYear('date', $year)->select('date', 'clock_in', 'clock_out', 'status', 'early_leaving', 'late', 'attendance_type_id', 'is_valid', 'shift_type_id', 'coord_in', 'work_hours')->get();

            $shift                  = ShiftTime::where('shift_type_id', $employee->shift_type->id)->select('is_working', 'days')->get()->pluck('is_working', 'days');
            $totalAttendance        = 0;
            $arrayAttendanceDate    = [];
            $totalLate              = 0;
            $totalLateTime          = 0;
            $totalExcessTime        = 0;
            $totalWorkTime          = 0;
            
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
                                $clock_out_data     = $attendance->clock_out ?? $attendance->clock_in;
                                $date_data          .= "{$attendance->clock_in} - {$clock_out_data}; ";
                                $totalAttendance    += 1;
                                $present            = true;

                                if ($attendance->is_valid && $attendance->attendance_type_id != '1') {
                                    $this->yellowed_cell->push($this->getColomnByDateAndEmployeeIndex($d, $index));
                                } else {
                                    if ($attendance->coord_in) {
                                        $coordinate         = explode(', ', $attendance->coord_in);
                                        $latitude           = $coordinate[0];
                                        $longitude          = $coordinate[1];
                                        $accuracy           = $coordinate[2];

                                        $branch_data        = Branch::where('id', $employee->branch_id)->first();
                                        $distance           = DistanceCalculator::haversineDistance($latitude, $longitude, (float)$branch_data['latitude'], (float)$branch_data['longitude']);

                                        if (($accuracy + (float)$branch_data['tolerance']) < $distance) {
                                            $this->yellowed_cell->push($this->getColomnByDateAndEmployeeIndex($d, $index));
                                        }
                                    }
                                }

                                // Set the workhours per attendance
                                if ($attendance->work_hours) {
                                    list($hours, $minutes, $seconds)    = explode(':', $attendance->work_hours);
                                    $attendanceWorkHours                = ($hours + $minutes / 60 + $seconds / 3600);

                                    // Convert time to seconds and add to total work hours time
                                    $totalWorkTime                      += $hours * 3600 + $minutes * 60 + $seconds;
                                } else {
                                    $attendanceWorkHours                = 0;
                                }

                                if ($attendance_shift->is_working) {
                                    if ((strtotime($attendance->clock_in) > (strtotime($attendance_shift->start_time) + ((int)$settings['late_tolerance'] * 60)))) {
                                        $totalLate += 1;
                                        $this->late_cell->push($this->getColomnByDateAndEmployeeIndex($d, $index));
    
                                        // Parse late time to extract hours, minutes, and seconds
                                        list($hours, $minutes, $seconds) = explode(':', $attendance->late);
                                        
                                        // Convert time to seconds and add to total late time
                                        $totalLateTime += $hours * 3600 + $minutes * 60 + $seconds;
                                    }

                                    // Calculate required work hours based on shift
                                    $startShift = strtotime($attendance_shift->start_time);
                                    $endShift   = strtotime($attendance_shift->end_time);

                                    if ($endShift < $startShift) {
                                        // Shift spans two dates, consider hours on the next day
                                        $endShift += 86400; // Add 24 hours
                                    }

                                    $requiredWorkHours = max(0, round(($endShift - $startShift) / 3600, 2));

                                     // Check if the work hours of attendance match the required work hours
                                    if ($attendanceWorkHours > $requiredWorkHours) {
                                        list($hours, $minutes, $seconds) = explode(':', $attendance->work_hours);
                                        $totalExcessTime += ($hours * 3600 + $minutes * 60 + $seconds) - max(0, round($endShift - $startShift, 2));
                                    }
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

            // calculating total late
            $totalLateHours     = floor($totalLateTime / 3600);
            $totalLateMinutes   = floor(($totalLateTime % 3600) / 60);
            $totalLateSeconds   = $totalLateTime % 60;

            // calculating excess time
            $totalExcessHours     = floor($totalExcessTime / 3600);
            $totalExcessMinutes   = floor(($totalExcessTime % 3600) / 60);
            $totalExcessSeconds   = $totalExcessTime % 60;
            
            // Calculating work hours
            $totalWorkHours     = floor($totalWorkTime / 3600);
            $totalWorkMinutes   = floor(($totalWorkTime % 3600) / 60);
            $totalWorkSeconds   = $totalWorkTime % 60;


            array_push($arrayAttendanceDate, $totalAttendance >= 1 ? $totalAttendance : '0', sprintf("%02d:%02d:%02d", $totalWorkHours, $totalWorkMinutes, $totalWorkSeconds), $totalLate >= 1 ? $totalLate : '0', sprintf("%02d:%02d:%02d", $totalLateHours, $totalLateMinutes, $totalLateSeconds), sprintf("%02d:%02d:%02d", $totalExcessHours, $totalExcessMinutes, $totalExcessSeconds));

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
                $sheet->mergeCells($relativeColomn['totalWorkColomn']);
                $sheet->mergeCells($relativeColomn['lateColomn']);
                $sheet->mergeCells($relativeColomn['lateTimeColomn']);
                $sheet->mergeCells($relativeColomn['excessTimeColomn']);
                $sheet->setCellValue($relativeColomn['totalCell'], "Total");
                $sheet->setCellValue($relativeColomn['totalWorkCell'], __('Total Work Time'));
                $sheet->setCellValue($relativeColomn['lateCell'], __('Total Late'));
                $sheet->setCellValue($relativeColomn['lateTimeCell'], __('Total Late Time'));
                $sheet->setCellValue($relativeColomn['excessTimeCell'], __('Total Excess Time'));
                $this->applyHeaderCellStyles($sheet, $relativeColomn['dateColomn']);
                $this->applyHeaderCellStyles($sheet, $relativeColomn['dateNumberColomn']);
                $this->applyHeaderCellStyles($sheet, $relativeColomn['totalColomn']);
                $this->applyHeaderCellStyles($sheet, $relativeColomn['totalWorkColomn']);
                $this->applyHeaderCellStyles($sheet, $relativeColomn['lateColomn']);
                $this->applyHeaderCellStyles($sheet, $relativeColomn['lateTimeColomn']);
                $this->applyHeaderCellStyles($sheet, $relativeColomn['excessTimeColomn']);
                
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
                $sheet->getStyle("{$relativeColomn['lastColomn']}7:{$relativeColomn['lastColomn']}{$this->total_data}")->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ]
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
        $lastDateColumn     = 'AI';
        $totalColumn        = 'AJ';
        $totalWorkColomn    = 'AK';
        $lateColumn         = 'AL';
        $lateTimeColumn     = 'AM';
        $excessTimeColumn   = 'AN';

        switch ($this->total_days) {
            case 28:
                $lastDateColumn     = 'AH';
                $totalColumn        = 'AI';
                $totalWorkColomn    = 'AJ';
                $lateColumn         = 'AK';
                $lateTimeColumn     = 'AL';
                $excessTimeColumn   = 'AM';
                break;
            case 29:
                $lastDateColumn     = 'AI';
                $totalColumn        = 'AJ';
                $totalWorkColomn    = 'AK';
                $lateColumn         = 'AL';
                $lateTimeColumn     = 'AM';
                $excessTimeColumn   = 'AN';
                break;
            case 30:
                $lastDateColumn     = 'AJ';
                $totalColumn        = 'AK';
                $totalWorkColomn    = 'AL';
                $lateColumn         = 'AM';
                $lateTimeColumn     = 'AN';
                $excessTimeColumn   = 'AO';
                break;
            case 31:
                $lastDateColumn     = 'AK';
                $totalColumn        = 'AL';
                $totalWorkColomn    = 'AM';
                $lateColumn         = 'AN';
                $lateTimeColumn     = 'AO';
                $excessTimeColumn   = 'AP';
                break;
        }
        return [
            'dateColomn'        => "G5:{$lastDateColumn}5",
            'dateNumberColomn'  => "G6:{$lastDateColumn}6",
            'totalColomn'       => "{$totalColumn}5:{$totalColumn}6",
            'totalCell'         => "{$totalColumn}5",
            'totalWorkColomn'   => "{$totalWorkColomn}5:{$totalWorkColomn}6",
            'totalWorkCell'     => "{$totalWorkColomn}5",
            'lateColomn'        => "{$lateColumn}5:{$lateColumn}6",
            'lateCell'          => "{$lateColumn}5",
            'lateTimeColomn'    => "{$lateTimeColumn}5:{$lateTimeColumn}6",
            'lateTimeCell'      => "{$lateTimeColumn}5",
            'excessTimeColomn'  => "{$excessTimeColumn}5:{$excessTimeColumn}6",
            'excessTimeCell'    => "{$excessTimeColumn}5",
            'lastColomn'        => $excessTimeColumn,
            'lastCell'          => "{$excessTimeColumn}6",
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
