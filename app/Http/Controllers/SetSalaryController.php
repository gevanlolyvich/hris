<?php

namespace App\Http\Controllers;

use App\Models\Allowance;
use App\Models\AllowanceOption;
use App\Models\Commission;
use App\Models\DeductionOption;
use App\Models\Employee;
use App\Models\Loan;
use App\Models\LoanOption;
use App\Models\OtherPayment;
use App\Models\Overtime;
use App\Models\PayslipType;
use App\Models\SaturationDeduction;
use App\Models\AttendanceEmployee;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SetSalaryController extends Controller
{
    function getTotalWorkdays($employeeWorkdays, $month, $year) {    
        // Get the number of days in the month
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    
        // Initialize the total workdays count
        $totalWorkdays = 0;
    
        // Loop through each day in the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            // Get the day of the week for the current day
            $currentDayName = date('l', strtotime("$year-$month-$day"));
    
            // Check if the current day is a workday for the employee
            if (in_array($currentDayName, $employeeWorkdays)) {
                $totalWorkdays++;
            }
        }
    
        return $totalWorkdays;
    }

    function getTotalHours($shiftTimes, $month, $year) {
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

    function getPresentDays($attendanceData, $shiftTimes, $type = 'Fixed') {
        // Initialize the present days count
        $presentDaysCount = 0;

        // Loop through each attendance entry
        foreach ($attendanceData as $attendance) {
            // Get the day of the week for the attendance date
            $attendanceDayName = date('l', strtotime($attendance['date']));
    
            // Check if the attendance date is a workday based on shift times
            $shift = collect($shiftTimes)->firstWhere('days', $attendanceDayName);
    
            if ($shift && $shift['is_working'] && $type == 'Fixed') {
                // Calculate required work hours based on shift

                $startShift = strtotime($shift['start_time']);
                $endShift   = strtotime($shift['end_time']);

                if ($endShift < $startShift) {
                    // Shift spans two dates, consider hours on the next day
                    $endShift += 86400; // Add 24 hours
                }

                if ($shift['end_time'] < $shift['start_time']) {
                    // Shift spans two dates, consider hours on the next day
                    $shift['end_time'] += 86400; // Add 24 hours
                }
                
                $requiredWorkHours = max(0, round(($endShift - $startShift) / 3600 - 1, 2));
    
                // Check if the work hours of attendance match the required work hours
                if ($attendance['work_hours']) {
                    list($hours, $minutes, $seconds) = explode(':', $attendance['work_hours']);
                    $attendanceWorkHours = ($hours + $minutes / 60 + $seconds / 3600) - 1;
                } else {
                    $attendanceWorkHours = 0;
                }
                
                if ($attendanceWorkHours >= $requiredWorkHours || $type != 'Fixed') {
                    // Increment the present days count
                    $presentDaysCount++;
                }
            } else {
                $presentDaysCount++;
            }
        }
    
        return $presentDaysCount;
    }

    public function index()
    {
        if(\Auth::user()->can('Manage Set Salary'))
        {
            $employees = !empty(\Auth::user()->branch_id) ? Employee::where('branch_id', \Auth::user()->branch_id)->where('is_active', 1)->orderby('name', 'asc')->get() : Employee::where('is_active', 1)->orderby('name', 'asc')->get();

            return view('setsalary.index', compact('employees'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit($id)
    {
        if(\Auth::user()->can('Edit Set Salary'))
        {
            $payslip_type      = PayslipType::get()->pluck('name', 'id');
            $allowance_options = AllowanceOption::get()->pluck('name', 'id');
            $loan_options      = LoanOption::get()->pluck('name', 'id');
            $deduction_options = DeductionOption::get()->pluck('name', 'id');
            if(\Auth::user()->type == 'employee')
            {
                $currentEmployee      = Employee::where('is_active', 1)->where('user_id', '=', \Auth::user()->id)->first();

                if (empty($currentEmployee) || !$currentEmployee) {
                    return redirect()->back()->with('error', __('Inactive'));
                }

                $allowances           = Allowance::where('employee_id', $currentEmployee->id)->get();
                $commissions          = Commission::where('employee_id', $currentEmployee->id)->get();
                $loans                = Loan::where('employee_id', $currentEmployee->id)->get();
                $saturationdeductions = SaturationDeduction::where('employee_id', $currentEmployee->id)->get();
                $otherpayments        = OtherPayment::where('employee_id', $currentEmployee->id)->get();
                $overtimes            = Overtime::where('employee_id', $currentEmployee->id)->get();
                $employee             = Employee::where('user_id', '=', \Auth::user()->id)->first();

                return view('setsalary.employee_salary', compact('employee', 'payslip_type', 'allowance_options', 'commissions', 'loan_options', 'overtimes', 'otherpayments', 'saturationdeductions', 'loans', 'deduction_options', 'allowances'));

            }
            else
            {
                $allowances           = Allowance::where('employee_id', $id)->get();
                $commissions          = Commission::where('employee_id', $id)->get();
                $loans                = Loan::where('employee_id', $id)->get();
                $saturationdeductions = SaturationDeduction::where('employee_id', $id)->get();
                $otherpayments        = OtherPayment::where('employee_id', $id)->get();
                $overtimes            = Overtime::where('employee_id', $id)->get();
                $employee             = Employee::where('is_active', 1)->find($id);

                return view('setsalary.edit', compact('employee', 'payslip_type', 'allowance_options', 'commissions', 'loan_options', 'overtimes', 'otherpayments', 'saturationdeductions', 'loans', 'deduction_options', 'allowances'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show($id, Request $request)
    {
        $year                 = $request->month ? date('Y', strtotime($request->month)) : date('Y');
        $month                = $request->month ? date('m', strtotime($request->month)): date('m');
        $start_date           = date($year . '-' . $month . '-01');
        $end_date             = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

        $payslip_type         = PayslipType::get()->pluck('name', 'id');
        $allowance_options    = AllowanceOption::get()->pluck('name', 'id');
        $loan_options         = LoanOption::get()->pluck('name', 'id');
        $deduction_options    = DeductionOption::get()->pluck('name', 'id');
        $employee             = \Auth::user()->type == 'employee' ? Employee::where('user_id', '=', \Auth::user()->id) : Employee::where('id', $id);
        
        $employee             = !empty(\Auth::user()->branch_id) ? $employee->where('branch_id', \Auth::user()->branch_id)->first() : $employee->first();

        if (empty($employee)) {
            return redirect()->back()->with('error', __('Permission denied'));
        }
        
        $allowances           = Allowance::where('employee_id', $employee->id)->whereMonth('date', $month)->whereYear('date', $year)->get();
        $commissions          = Commission::where('employee_id', $employee->id)->whereMonth('date', $month)->whereYear('date', $year)->get();
        $loans                = Loan::where('employee_id', $employee->id)->whereMonth('end_date', $month)->whereYear('end_date', $year)->get();
        $saturationdeductions = SaturationDeduction::where('employee_id', $employee->id)->get();
        $otherpayments        = OtherPayment::where('employee_id', $employee->id)->get();
        $overtimes            = Overtime::where('employee_id', $employee->id)->whereMonth('date', $month)->whereYear('date', $year)->whereNotNull(['report_document'])->get();

        $total_work_days      = $this->getTotalWorkdays($employee->shift_type->shiftTimes->where('is_working', 1)->pluck('days')->toArray(), $month, $year);
        $total_work_hours     = $this->getTotalHours($employee->shift_type->shiftTimes->where('is_working', 1), $month, $year);
        $total_present_days   = $this->getPresentDays(AttendanceEmployee::where('employee_id', $employee->id)->whereMonth('date', $month)->whereYear('date', $year)->where('is_valid', 1)->select('date', 'status', 'work_hours', 'is_valid')->get()->toArray(), $employee->shift_type->shiftTimes->where('is_working', 1), $employee->employeeType->type);

        foreach ( $allowances as  $value) {
            if($value->type == 'percentage' )
        {
            $employee           = Employee::find($value->employee_id);
            $empsal             = $value->amount * $employee->salary / 100;
            $value->tota_allow  = $empsal;
            }
        }

        foreach ( $commissions as  $value) {
            if(  $value->type == 'percentage' )
        {
            $empsal            = $value->amount * $employee->salary / 100;
            $value->tota_allow = $empsal;
            }
        }

        foreach ( $loans as  $value) {
            if(  $value->type == 'percentage' )
        {
            $employee          = Employee::find($value->employee_id);
            $empsal  = $value->amount * $employee->salary / 100;
            $value->tota_allow = $empsal;
            }
        }

        foreach ( $saturationdeductions as  $value) {
            if(  $value->type == 'percentage' )
        {
            $employee          = Employee::find($value->employee_id);
            $empsal  = $value->amount * $employee->salary / 100;
            $value->tota_allow = $empsal;
            }
        }

        foreach ( $otherpayments as  $value) {
            if(  $value->type == 'percentage' )
        {
            $employee          = Employee::find($value->employee_id);
            $empsal  = $value->amount * $employee->salary / 100;
            $value->tota_allow = $empsal;
            }
        }

        return view('setsalary.employee_salary', compact('employee', 'payslip_type', 'allowance_options', 'commissions', 'loan_options', 'overtimes', 'otherpayments', 'saturationdeductions', 'loans', 'deduction_options', 'allowances', 'total_work_days', 'total_work_hours', 'total_present_days'));
    }


    public function employeeUpdateSalary(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(), [
                               'salary_type' => 'required',
                               'salary' => 'required',
                           ]
        );
        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }
        $employee = Employee::findOrFail($id);
        $input    = $request->all();
        $employee->fill($input)->save();

        return redirect()->back()->with('success', 'Employee Salary Updated.');
    }

    public function employeeSalary()
    {
        if(\Auth::user()->type == "employee")
        {
            $employees = !empty(\Auth::user()->branch_id) ? Employee::where('branch_id', \Auth::user()->branch_id)->where('user_id', \Auth::user()->id)->orderby('name', 'asc')->get() : Employee::where('user_id', \Auth::user()->id)->orderby('name', 'asc')->get();

            return view('setsalary.index', compact('employees'));
        }
    }

    public function employeeBasicSalary($id)
    {

        $payslip_type = PayslipType::get()->pluck('name', 'id');
        $employee     = Employee::find($id);

        return view('setsalary.basic_salary', compact('employee', 'payslip_type'));
    }
}
