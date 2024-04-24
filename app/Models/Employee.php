<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use SebastianBergmann\CodeCoverage\Percentage;
use Illuminate\Support\Facades\Log;

class Employee extends Model
{
    use SoftDeletes;

    protected $table = 'employees';
    protected $fillable = [
        'user_id',
        'personel_id',
        'shift_type_id',
        'managed_by',
        'name',
        'type_id',
        'dob',
        'gender',
        'phone',
        'address',
        'domicile_address',
        'marital_status',
        'dependents',
        'emergency_contact_number',
        'emergency_contact_relation',
        'emergency_contact_photo',
        'email',
        'password',
        'employee_id',
        'branch_id',
        'department_id',
        'designation_id',
        'company_doj',
        'nationality',
        'identity_type',
        'identity_number',
        'documents',
        'account_holder_name',
        'account_number',
        'bank_id',
        'tax_payer_id',
        'salary_type',
        'salary',
        'created_by',
    ];

    function getTotalWorkdays($employeeWorkdays, $month, $year)
    {
        // Get the number of days in the month
        $daysInMonth    = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $holidays       = Holiday::whereMonth('start_date', '<=', $month)->whereMonth('end_date', '>=', $month)->whereYear('start_date', '<=', $year)->whereYear('end_date', '>=', $year)->get();

        // Initialize the total workdays count
        $totalWorkdays = 0;

        // Loop through each day in the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            // Get the day of the week for the current day
            $currentDayName = date('l', strtotime("$year-$month-$day"));
            $date           = $day <= 9 ? "$year-$month-0$day" : "$year-$month-$day";

            $holiday    = $holidays->where('start_date', '<=', $date)->where('end_date', '>=', $date)->values();

            // Check if the current day is a workday for the employee
            if (in_array($currentDayName, $employeeWorkdays) && $holiday->isEmpty()) {
                $totalWorkdays++;
            }
        }

        return $totalWorkdays;
    }

    function getTotalHours($shiftTimes, $month, $year)
    {
        // Initialize the total working hours count
        $totalWorkingHours = 0;

        // Loop through each day in the month
        for ($day = 1; $day <= cal_days_in_month(CAL_GREGORIAN, $month, $year); $day++) {
            // Get the day of the week for the current day
            $currentDayName = date('l', strtotime("$year-$month-$day"));

            // Check if the current day is a workday for the employee
            $shift = collect($shiftTimes)->firstWhere('days', $currentDayName);

            if ($shift && $shift['is_working']) {
                // Calculate working hours for the day (subtract 1 hour for break time)
                $startTimestamp = strtotime("$year-$month-$day " . $shift['start_time']);
                $endTimestamp = strtotime("$year-$month-$day " . $shift['end_time']);

                if ($endTimestamp < $startTimestamp) {
                    // Shift spans two dates, consider hours on the next day
                    $endTimestamp += 86400; // Add 24 hours
                }

                // Subtract 1 hour for break time
                $workingHours = max(0, round(($endTimestamp - $startTimestamp) / 3600 - 1, 2));

                // Add working hours to the total
                $totalWorkingHours += $workingHours;
            }
        }

        return $totalWorkingHours;
    }

    function getPresentDays($attendanceData, $shiftTimes, $type = '')
    {
        // Initialize the present days count
        $presentDaysCount = 0;

        // Loop through each attendance entry
        foreach ($attendanceData as $attendance) {
            // Get the day of the week for the attendance date
            $attendanceDayName = date('l', strtotime($attendance->date));

            // Check if the attendance date is a workda based on shift times
            $shift = $attendance->shift_type?->shiftTimes?->firstWhere('days', $attendanceDayName);

            if ($shift && $shift->is_working && $type == 'Fixed') {
                // Calculate required work hours based on shift
                $startShift = strtotime($shift->start_time);
                $endShift   = strtotime($shift->end_time);

                if ($endShift < $startShift) {
                    // Shift spans two dates, consider hours on the next day
                    $endShift += 86400; // Add 24 hours
                }

                $requiredWorkHours = max(0, round(($endShift - $startShift) / 3600 - 1, 2));

                // Check if the work hours of attendance match the required work hours
                if ($attendance->work_hours) {
                    list($hours, $minutes, $seconds) = explode(':', $attendance->work_hours);
                    $attendanceWorkHours = ($hours + $minutes / 60 + $seconds / 3600) - 1;
                } else {
                    $attendanceWorkHours = 0;
                }

                if ($attendance->status != 'Present') {
                    // Increment the present days count
                    $presentDaysCount++;
                } elseif ($attendanceWorkHours >= $requiredWorkHours || $type != 'Fixed') {
                    // Increment the present days count
                    $presentDaysCount++;
                }
            } else {
                $presentDaysCount++;
            }
        }

        return $presentDaysCount;
    }

    public function attendanceRequests()
    {
        return $this->hasMany(AttendanceRequest::class, 'employee_id', 'id');
    }

    public function shift_type()
    {
        return $this->belongsTo(ShiftType::class, 'shift_type_id', 'id')->withTrashed();;
    }

    public function documents()
    {
        return $this->hasMany('App\Models\EmployeeDocument', 'employee_id', 'id')->get();
    }

    public function salary_type()
    {
        return $this->hasOne('App\Models\PayslipType', 'id', 'salary_type')->pluck('name')->first();
    }
    public function bank()
    {
        return $this->belongsTo('App\Models\Bank', 'bank_id', 'id');
    }
    public function direct_spv()
    {
        return $this->belongsTo('App\Models\Employee', 'managed_by');
    }

    public function get_salary($month, $year)
    {
        $employee               = Employee::find($this->id);
        $total_work_days        = $this->getTotalWorkdays($employee->shift_type->shiftTimes->where('is_working', 1)->pluck('days')->toArray(), $month, $year);
        $total_present_days     = $this->getPresentDays(AttendanceEmployee::where('employee_id', $employee->id)->whereMonth('date', $month)->whereYear('date', $year)->where('is_valid', 1)->select('date', 'status', 'work_hours', 'is_valid', 'shift_type_id')->get(), $employee->shift_type->shiftTimes->where('is_working', 1), $employee->employeeType->type);
        $fixed_rate             = ($total_present_days / $total_work_days) <= 1 ? $total_present_days / $total_work_days : 1;
        $normal_salary          = $employee->employeeType->type == 'Fixed' ? (!empty($employee->salary) ? $employee->salary : 0) * $fixed_rate : (!empty($employee->salary) ? $employee->salary : 0) * $total_present_days;

        return $normal_salary;
    }

    public function get_net_salary($month, $year)
    {
        $employee                = Employee::find($this->id);
        // $total_work_days      = $this->getTotalWorkdays($employee->shift_type->shiftTimes->where('is_working', 1)->pluck('days')->toArray(), $month, $year);
        $total_work_hours        = $this->getTotalHours($employee->shift_type->shiftTimes->where('is_working', 1), $month, $year);
        // $total_present_days   = $this->getPresentDays(AttendanceEmployee::where('employee_id', $employee->id)->whereMonth('date', $month)->whereYear('date', $year)->where('is_valid', 1)->select('date', 'status', 'work_hours', 'is_valid')->get()->toArray(), $employee->shift_type->shiftTimes->where('is_working', 1));

        // Normal Salary Calculate
        $normal_salary  = $this->get_bruto_salary($month, $year);

        //Loan
        $loans      = Loan::where('employee_id', '=', $this->id)->whereMonth('end_date', $month)->whereYear('end_date', $year)->get();
        $total_loan = 0;
        foreach ($loans as $loan) {
            if ($loan->type == 'percentage') {
                $total_loan  = $loan->amount * $employee->salary / 100   + $total_loan;
            } else {
                $total_loan = $loan->amount + $total_loan;
            }
        }

        //Saturation Deduction
        $saturation_deductions      = SaturationDeduction::where('employee_id', '=', $this->id)->get();
        $total_saturation_deduction = 0;
        foreach ($saturation_deductions as $saturation_deduction) {
            if ($saturation_deduction->type == 'percentage') {
                $total_saturation_deduction  = $saturation_deduction->amount * $employee->salary / 100 + $total_saturation_deduction;
            } else {
                $total_saturation_deduction = $saturation_deduction->amount + $total_saturation_deduction;
            }
        }

        //Net Salary Calculate
        $deduction_salary = $total_loan - $total_saturation_deduction;

        $net_salary     =  $normal_salary + $deduction_salary;

        return $net_salary;
    }

    public function get_bruto_salary($month, $year)
    {
        $employee                = Employee::find($this->id);
        // $total_work_days      = $this->getTotalWorkdays($employee->shift_type->shiftTimes->where('is_working', 1)->pluck('days')->toArray(), $month, $year);
        $total_work_hours        = $this->getTotalHours($employee->shift_type->shiftTimes->where('is_working', 1), $month, $year);
        // $total_present_days   = $this->getPresentDays(AttendanceEmployee::where('employee_id', $employee->id)->whereMonth('date', $month)->whereYear('date', $year)->where('is_valid', 1)->select('date', 'status', 'work_hours', 'is_valid')->get()->toArray(), $employee->shift_type->shiftTimes->where('is_working', 1));

        //allowance
        $allowances      = Allowance::where('employee_id', '=', $this->id)->whereMonth('date', $month)->whereYear('date', $year)->get();
        $total_allowance = 0;
        foreach ($allowances as $allowance) {
            if ($allowance->type == 'percentage') {
                $total_allowance  = $allowance->amount * $employee->salary / 100  + $total_allowance;
            } else {
                $total_allowance = $allowance->amount + $total_allowance;
            }
        }

        //commission
        $commissions      = Commission::where('employee_id', '=', $this->id)->whereMonth('date', $month)->whereYear('date', $year)->get();
        $total_commission = 0;
        foreach ($commissions as $commission) {
            if ($commission->type == 'percentage') {
                $total_commission  = $commission->amount * $employee->salary / 100 + $total_commission;
            } else {
                $total_commission = $commission->amount + $total_commission;
            }
        }

        //OtherPayment
        $other_payments      = OtherPayment::where('employee_id', '=', $this->id)->get();
        $total_other_payment = 0;
        foreach ($other_payments as $other_payment) {
            if ($other_payment->type == 'percentage') {
                $total_other_payment  = $other_payment->amount * $employee->salary / 100  + $total_other_payment;
            } else {
                $total_other_payment = $other_payment->amount + $total_other_payment;
            }
        }

        //Overtime
        $over_times             = Overtime::where('employee_id', $employee->id)->whereMonth('date', $month)->whereYear('date', $year)->whereNotNull(['report_document'])->get();
        $total_over_time        = 0;
        $total_over_time_hours  = 0;
        $overtime_limit         = $employee?->departments?->overtime_limit;
        foreach ($over_times as $over_time) {
            // $total_hours        = $over_time->type == 'daily' ? 8 : max(0, round((strtotime($over_time->clock_out) - strtotime($over_time->clock_in)) / 3600, 2));
            $total_hours = 0;
            if ($over_time->type == 'daily') {
                $total_hours = 8;
            } else {
                if (date('Y-m-d', strtotime($over_time->clock_out)) != date('Y-m-d', strtotime($over_time->clock_in))) {
                    $end = date('Y-m-d', strtotime($over_time->clock_in . ' +1 day'));
                    $total_hours = max(0, round((strtotime($end) - strtotime($over_time->clock_in)) / 3600, 2));
                } else {
                    $total_hours = max(0, round((strtotime($over_time->clock_out) - strtotime($over_time->clock_in)) / 3600, 2));
                }
            }

            if ($overtime_limit) {
                if ($total_over_time_hours >= $overtime_limit) {
                    continue;
                }
                if (($total_over_time_hours + $total_hours) >= $overtime_limit) {
                    $total_hours =  $overtime_limit - $total_over_time_hours;
                }
                $total_over_time_hours += $total_hours;
            }
            $amount             = $over_time->is_work_day ? $total_hours * ($over_time->employee->salary / $total_work_hours) : $total_hours * ($over_time->employee->salary / $total_work_hours) * 2;
            $total_over_time    = $amount + $total_over_time;
        }

        // Normal Salary Calculate
        $normal_salary  = $this->get_salary($month, $year);

        //Net Salary Calculate
        $advance_salary = $total_allowance + $total_commission + $total_other_payment + $total_over_time;

        $bruto     =  $normal_salary + $advance_salary;

        return $bruto;
    }

    public static function allowance($id, $month, $year)
    {
        $allowances      = Allowance::where('employee_id', '=', $id)->whereMonth('date', $month ?? date('m'))->whereYear('date', $year ?? date('Y'))->get();
        $total_allowance = 0;
        foreach ($allowances as $allowance) {
            $total_allowance = $allowance->amount + $total_allowance;
        }

        $allowance_json = json_encode($allowances);

        return $allowance_json;
    }

    public static function commission($id, $month, $year)
    {
        //commission
        $commissions      = Commission::where('employee_id', '=', $id)->whereMonth('date', $month ?? date('m'))->whereYear('date', $year ?? date('Y'))->get();
        // dd($commissions);
        $total_commission = 0;

        foreach ($commissions as $commission) {
            $total_commission = $commission->amount + $total_commission;
        }
        $commission_json = json_encode($commissions);

        return $commission_json;
    }

    public static function loan($id, $month, $year)
    {
        //Loan
        $loans      = Loan::where('employee_id', '=', $id)->whereMonth('end_date', $month ?? date('m'))->whereYear('end_date', $year ?? date('Y'))->get();
        $total_loan = 0;
        foreach ($loans as $loan) {
            $total_loan = $loan->amount + $total_loan;
        }
        $loan_json = json_encode($loans);

        return $loan_json;
    }

    public static function saturation_deduction($id)
    {
        //Saturation Deduction
        $saturation_deductions      = SaturationDeduction::where('employee_id', '=', $id)->get();
        $total_saturation_deduction = 0;
        foreach ($saturation_deductions as $saturation_deduction) {
            $total_saturation_deduction = $saturation_deduction->amount + $total_saturation_deduction;
        }
        $saturation_deduction_json = json_encode($saturation_deductions);

        return $saturation_deduction_json;
    }

    public static function other_payment($id)
    {
        //OtherPayment
        $other_payments      = OtherPayment::where('employee_id', '=', $id)->get();
        $total_other_payment = 0;
        foreach ($other_payments as $other_payment) {
            $total_other_payment = $other_payment->amount + $total_other_payment;
        }
        $other_payment_json = json_encode($other_payments);

        return $other_payment_json;
    }

    public function overtime(): HasMany
    {
        return $this->hasMany(Overtime::class, 'employee_id');
    }

    public static function get_overtime($id, $month, $year)
    {
        //Overtime
        $over_times      = Overtime::where('employee_id', '=', $id)->whereMonth('date', $month ?? date('m'))->whereYear('date', $year ?? date('Y'))->whereNotNull(['report_document', 'clock_in', 'clock_out'])->get();
        $over_time_json = json_encode($over_times);

        return $over_time_json;
    }

    public static function employee_id()
    {
        $employee = Employee::latest()->first();

        return !empty($employee) ? $employee->id + 1 : 1;
    }

    public function branch()
    {
        return $this->hasOne('App\Models\Branch', 'id', 'branch_id');
    }

    public function phone()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'phone');
    }

    public function department()
    {
        return $this->hasOne('App\Models\Department', 'id', 'department_id');
    }

    public function departments()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function designation()
    {
        return $this->hasOne('App\Models\Designation', 'id', 'designation_id');
    }

    public function salaryType()
    {
        return $this->belongsTo(PayslipType::class, 'salary_type', 'id');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function paySlip()
    {
        return $this->hasOne('App\Models\PaySlip', 'id', 'employee_id');
    }


    public function present_status($employee_id, $data)
    {
        return AttendanceEmployee::where('employee_id', $employee_id)->where('date', $data)->first();
    }
    public static function employee_name($name)
    {

        $employee = Employee::where('id', $name)->first();
        if (!empty($employee)) {
            return $employee->name;
        }
    }

    public static function login_user($name)
    {
        $user = User::where('id', $name)->first();
        return $user->name;
    }

    public static function employee_salary($salary)
    {

        $employee = Employee::where("salary", $salary)->first();
        // dd($employee);
        if ($employee->salary == '0' || $employee->salary == '0.0') {
            return "-";
        } else {
            return $employee->salary;
        }
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'managed_by', 'id');
    }
    
    public function recursiveManager(): BelongsTo
    {
        return $this->manager()->with('recursiveManager');
    }
    
    public function managersFlatten()
    {
        $result = collect();
        $item = $this->recursiveManager;
        if ($item instanceof Employee) {
            $result->push($item);
            $result = $result->merge($item->managersFlatten());
        }
        
        return $result;
    }
    
    public function subordinate(): HasMany
    {
        return $this->hasMany(self::class, 'managed_by');
    }

    public function subordinateRecursive(): HasMany
    {
        return $this->subordinate()->with('subordinateRecursive');
    }
    
    public function subordinatesFlatten()
    {
        $result = collect();
        $subordinates = $this->subordinateRecursive;
        
        foreach ($subordinates as $subordinate) {
            if ($subordinate instanceof Employee) {
                $result->push($subordinate);
                $result = $result->merge($subordinate->subordinatesFlatten());
            }
        }
        
        return $result;
    }
    
    public function shift_histories(): HasMany
    {
        return $this->hasMany(ShiftHistory::class);
    }

    public function assignment(): HasMany
    {
        return $this->hasMany(EventEmployee::class, 'employee_id');
    }
    
    public function home_histories(): HasMany
    {
        return $this->hasMany(EmployeeHomeHistory::class);
    }
    
    public static $employeeTypes = [
        'full time' => 'Full Time',
        'daily worker' => 'Daily Worker',
    ];

    public function getNameBranch()
    {
        return $this->name . '|' . $this->branch->name;
    }

    public function employeeType(): BelongsTo
    {
        return $this->belongsTo(EmployeeType::class, 'type_id', 'id');
    }

    public function attendances()
    {
        return $this->hasMany(AttendanceEmployee::class, 'employee_id', 'id');
    }
}
