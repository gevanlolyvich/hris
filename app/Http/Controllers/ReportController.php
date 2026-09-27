<?php

namespace App\Http\Controllers;

use App\Exports\accountstatementExport;
use App\Exports\LeaveReportExport;
use App\Exports\PayrollExport;
use App\Exports\TimesheetReportExport;
use App\Models\AccountList;
use App\Models\AttendanceEmployee;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Deposit;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\PaySlip;
use App\Models\TimeSheet;
use App\Models\ShiftTime;
use App\Models\Overtime;
use App\Models\Holiday;
use App\Utilities\AttendanceLocationResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MonthlyAttendanceExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{

    public function incomeVsExpense(Request $request)
    {
        if (\Auth::user()->can('Manage Report')) {
            // $deposit = Deposit::where('created_by', \Auth::user()->creatorId());

            $labels = $data = [];
            $expenseCount = $incomeCount = 0;
            if (!empty($request->start_month) && !empty($request->end_month)) {

                $start = strtotime($request->start_month);
                $end = strtotime($request->end_month);

                $currentdate = $start;
                $month = [];
                while ($currentdate <= $end) {
                    $month = date('m', $currentdate);
                    $year = date('Y', $currentdate);

                    $depositFilter = Deposit::whereMonth('date', $month)->whereYear('date', $year)->get();

                    $depositsTotal = 0;
                    foreach ($depositFilter as $deposit) {
                        $depositsTotal += $deposit->amount;
                    }
                    $incomeData[] = $depositsTotal;
                    $incomeCount += $depositsTotal;

                    $expenseFilter = Expense::whereMonth('date', $month)->whereYear('date', $year)->get();
                    $expenseTotal = 0;
                    foreach ($expenseFilter as $expense) {
                        $expenseTotal += $expense->amount;
                    }
                    $expenseData[] = $expenseTotal;
                    $expenseCount += $expenseTotal;

                    $labels[] = date('M Y', $currentdate);
                    $currentdate = strtotime('+1 month', $currentdate);
                }

                $filter['startDateRange'] = date('M-Y', strtotime($request->start_month));
                $filter['endDateRange'] = date('M-Y', strtotime($request->end_month));
            } else {
                for ($i = 0; $i < 6; $i++) {
                    $month = date('m', strtotime("-$i month"));
                    $year = date('Y', strtotime("-$i month"));

                    $depositFilter = Deposit::whereMonth('date', $month)->whereYear('date', $year)->get();

                    $depositTotal = 0;
                    foreach ($depositFilter as $deposit) {
                        $depositTotal += $deposit->amount;
                    }

                    $incomeData[] = $depositTotal;
                    $incomeCount += $depositTotal;

                    $expenseFilter = Expense::whereMonth('date', $month)->whereYear('date', $year)->get();
                    $expenseTotal = 0;
                    foreach ($expenseFilter as $expense) {
                        $expenseTotal += $expense->amount;
                    }
                    $expenseData[] = $expenseTotal;
                    $expenseCount += $expenseTotal;

                    $labels[] = date('M Y', strtotime("-$i month"));
                }
                $filter['startDateRange'] = date('M-Y');
                $filter['endDateRange'] = date('M-Y', strtotime("-5 month"));
            }

            $incomeArr['name'] = __('Income');
            $incomeArr['data'] = $incomeData;

            $expenseArr['name'] = __('Expense');
            $expenseArr['data'] = $expenseData;

            $data[] = $incomeArr;
            $data[] = $expenseArr;

            return view('report.income_expense', compact('labels', 'data', 'incomeCount', 'expenseCount', 'filter'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function leave(Request $request)
    {
        if (\Auth::user()->can('Manage Report')) {
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

            $department = $branch_id?->isNotEmpty() ? Department::whereIn('branch_id', $branch_id)->get()->pluck('name', 'id') : Department::get()->pluck('name', 'id');

            if (empty($childrenbranch_id)) {
                $branch->prepend('All', '');
                $department->prepend('All', '');
            }

            $filterYear['branch'] = __('All');
            $filterYear['department'] = __('All');
            $filterYear['type'] = __('Monthly');
            $filterYear['dateYearRange'] = date('M-Y');
            $employees = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->orderby('name', 'ASC') : Employee::orderby('name', 'ASC');
            if (!empty($request->branch)) {
                $employees->where('branch_id', $request->branch);
                $filterYear['branch'] = !empty(Branch::find($request->branch)) ? Branch::find($request->branch)->name : '';
            }
            if (!empty($request->department)) {
                $employees->where('department_id', $request->department);
                $filterYear['department'] = !empty(Department::find($request->department)) ? Department::find($request->department)->name : '';
            }

            $employees = $employees->get();

            $leaves = [];
            $totalApproved = $totalReject = $totalPending = 0;
            foreach ($employees as $employee) {

                $employeeLeave['id'] = $employee->id;
                $employeeLeave['employee_id'] = $employee->employee_id;
                $employeeLeave['employee'] = $employee->name;

                $approved = Leave::where('employee_id', $employee->id)->where('status', 'Approved');
                $reject = Leave::where('employee_id', $employee->id)->where('status', 'Reject');
                $pending = Leave::where('employee_id', $employee->id)->where('status', 'Pending');

                if ($request->type == 'monthly' && !empty($request->month)) {
                    $month = date('m', strtotime($request->month));
                    $year = date('Y', strtotime($request->month));

                    $approved->whereMonth('applied_on', $month)->whereYear('applied_on', $year);
                    $reject->whereMonth('applied_on', $month)->whereYear('applied_on', $year);
                    $pending->whereMonth('applied_on', $month)->whereYear('applied_on', $year);

                    $filterYear['dateYearRange'] = date('M-Y', strtotime($request->month));
                    $filterYear['type'] = __('Monthly');
                } elseif (!isset($request->type)) {
                    $month = date('m');
                    $year = date('Y');
                    $monthYear = date('Y-m');

                    $approved->whereMonth('applied_on', $month)->whereYear('applied_on', $year);
                    $reject->whereMonth('applied_on', $month)->whereYear('applied_on', $year);
                    $pending->whereMonth('applied_on', $month)->whereYear('applied_on', $year);

                    $filterYear['dateYearRange'] = date('M-Y', strtotime($monthYear));
                    $filterYear['type'] = __('Monthly');
                }

                if ($request->type == 'yearly' && !empty($request->year)) {
                    $approved->whereYear('applied_on', $request->year);
                    $reject->whereYear('applied_on', $request->year);
                    $pending->whereYear('applied_on', $request->year);

                    $filterYear['dateYearRange'] = $request->year;
                    $filterYear['type'] = __('Yearly');
                }

                $approved = $approved->count();
                $reject = $reject->count();
                $pending = $pending->count();

                $totalApproved += $approved;
                $totalReject += $reject;
                $totalPending += $pending;

                $employeeLeave['approved'] = $approved;
                $employeeLeave['reject'] = $reject;
                $employeeLeave['pending'] = $pending;

                $leaves[] = $employeeLeave;
            }

            $starting_year = date('Y', strtotime('-5 year'));
            $ending_year = date('Y', strtotime('+5 year'));

            $filterYear['starting_year'] = $starting_year;
            $filterYear['ending_year'] = $ending_year;

            $filter['totalApproved'] = $totalApproved;
            $filter['totalReject'] = $totalReject;
            $filter['totalPending'] = $totalPending;

            return view('report.leave', compact('department', 'branch', 'leaves', 'filterYear', 'filter'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function employeeLeave(Request $request, $employee_id, $status, $type, $month, $year)
    {
        if (\Auth::user()->can('Manage Report')) {
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

            $leaveTypes = LeaveType::get();
            $leaves = [];
            foreach ($leaveTypes as $leaveType) {
                $leave = new Leave();
                $leave->title = $leaveType->title;
                $totalLeave = $branch_id?->isNotEmpty() ? Leave::whereHas('employees', function ($query) use ($branch_id) {
                    $query->whereIn('branch_id', $branch_id);
                })->where('employee_id', $employee_id)->where('status', $status)->where('leave_type_id', $leaveType->id) : Leave::where('employee_id', $employee_id)->where('status', $status)->where('leave_type_id', $leaveType->id);
                if ($type == 'yearly') {
                    $totalLeave->whereYear('applied_on', $year);
                } else {
                    $m = date('m', strtotime($month));
                    $y = date('Y', strtotime($month));

                    $totalLeave->whereMonth('applied_on', $m)->whereYear('applied_on', $y);
                }
                $totalLeave = $totalLeave->count();

                $leave->total = $totalLeave;
                $leaves[] = $leave;
            }

            $leaveData = $branch_id?->isNotEmpty() ? Leave::whereHas('employees', function ($query) use ($branch_id) {
                $query->whereIn('branch_id', $branch_id);
            })->where('employee_id', $employee_id)->where('status', $status) : Leave::where('employee_id', $employee_id)->where('status', $status);
            $leaveData = $branch_id?->isNotEmpty() ? Leave::whereHas('employees', function ($query) use ($branch_id) {
                $query->whereIn('branch_id', $branch_id);
            })->where('employee_id', $employee_id)->where('status', $status) : Leave::where('employee_id', $employee_id)->where('status', $status);
            if ($type == 'yearly') {
                $leaveData->whereYear('applied_on', $year);
            } else {
                $m = date('m', strtotime($month));
                $y = date('Y', strtotime($month));

                $leaveData->whereMonth('applied_on', $m)->whereYear('applied_on', $y);
            }

            $leaveData = $leaveData->get();

            return view('report.leaveShow', compact('leaves', 'leaveData'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function accountStatement(Request $request)
    {
        if (\Auth::user()->can('Manage Report')) {
            $accountList = AccountList::get()->pluck('account_name', 'id');
            $accountList->prepend('All', '');

            $filterYear['account'] = __('All');
            $filterYear['type'] = __('Income');

            if ($request->type == 'expense') {
                $accountData = Expense::orderBy('id');
                $accounts = Expense::select('account_lists.id', 'account_lists.account_name')->leftjoin('account_lists', 'expenses.account_id', '=', 'account_lists.id')->groupBy('expenses.account_id')->selectRaw('sum(amount) as total');

                if (!empty($request->start_month) && !empty($request->end_month)) {
                    $start = strtotime($request->start_month);
                    $end = strtotime($request->end_month);
                } else {
                    $start = strtotime(date('Y-m'));
                    $end = strtotime(date('Y-m', strtotime("-5 month")));
                }

                $currentdate = $start;

                while ($currentdate <= $end) {
                    $data['month'] = date('m', $currentdate);
                    $data['year'] = date('Y', $currentdate);

                    $accountData->Orwhere(
                        function ($query) use ($data) {
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                        }
                    );

                    $accounts->Orwhere(
                        function ($query) use ($data) {
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                        }
                    );

                    $currentdate = strtotime('+1 month', $currentdate);
                }

                $filterYear['startDateRange'] = date('M-Y', $start);
                $filterYear['endDateRange'] = date('M-Y', $end);

                if (!empty($request->account)) {
                    $accountData->where('account_id', $request->account);
                    $accounts->where('account_lists.id', $request->account);

                    $filterYear['account'] = !empty(AccountList::find($request->account)) ? Department::find($request->account)->account_name : '';
                }

                $accounts->where('expenses.created_by', \Auth::user()->creatorId());

                $filterYear['type'] = __('Expense');
            } else {
                $accountData = Deposit::orderBy('id');
                $accounts = Deposit::select('account_lists.id', 'account_lists.account_name')->leftjoin('account_lists', 'deposits.account_id', '=', 'account_lists.id')->groupBy('deposits.account_id')->selectRaw('sum(amount) as total');

                if (!empty($request->start_month) && !empty($request->end_month)) {

                    $start = strtotime($request->start_month);
                    $end = strtotime($request->end_month);
                } else {
                    $start = strtotime(date('Y-m'));
                    $end = strtotime(date('Y-m', strtotime("-5 month")));
                }

                $currentdate = $start;

                while ($currentdate <= $end) {
                    $data['month'] = date('m', $currentdate);
                    $data['year'] = date('Y', $currentdate);

                    $accountData->Orwhere(
                        function ($query) use ($data) {
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                        }
                    );
                    $currentdate = strtotime('+1 month', $currentdate);

                    $accounts->Orwhere(
                        function ($query) use ($data) {
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                        }
                    );
                    $currentdate = strtotime('+1 month', $currentdate);
                }

                $filterYear['startDateRange'] = date('M-Y', $start);
                $filterYear['endDateRange'] = date('M-Y', $end);

                if (!empty($request->account)) {
                    $accountData->where('account_id', $request->account);
                    $accounts->where('account_lists.id', $request->account);

                    $filterYear['account'] = !empty(AccountList::find($request->account)) ? Department::find($request->account)->account_name : '';
                }
                $accounts->where('deposits.created_by', \Auth::user()->creatorId());
            }

            $accountData->where('created_by', \Auth::user()->creatorId());
            $accountData = $accountData->get();

            $accounts = $accounts->get();

            return view('report.account_statement', compact('accountData', 'accountList', 'accounts', 'filterYear'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function payroll(Request $request)
    {

        if (\Auth::user()->can('Manage Report')) {
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

            $department = $branch_id?->isNotEmpty() ? Department::whereIn('branch_id', $branch_id)->get()->pluck('name', 'id') : Department::get()->pluck('name', 'id');

            if (empty($branch_id?->isNotEmpty())) {
                $branch->prepend('All', '');
                $department->prepend('All', '');
            }

            $filterYear['branch'] = __('All');
            $filterYear['department'] = __('All');
            $filterYear['type'] = __('Monthly');

            $payslips = PaySlip::select('pay_slips.*', 'employees.name')->leftjoin('employees', 'pay_slips.employee_id', '=', 'employees.id');

            if ($request->type == 'monthly' && !empty($request->month)) {

                $payslips->where('salary_month', $request->month);

                $filterYear['dateYearRange'] = date('M-Y', strtotime($request->month));
                $filterYear['type'] = __('Monthly');
            } elseif (!isset($request->type)) {
                $month = date('Y-m');

                $payslips->where('salary_month', $month);

                $filterYear['dateYearRange'] = date('M-Y', strtotime($month));
                $filterYear['type'] = __('Monthly');
            }

            if ($request->type == 'yearly' && !empty($request->year)) {
                $startMonth = $request->year . '-01';
                $endMonth = $request->year . '-12';
                $payslips->where('salary_month', '>=', $startMonth)->where('salary_month', '<=', $endMonth);

                $filterYear['dateYearRange'] = $request->year;
                $filterYear['type'] = __('Yearly');
            }

            if (!empty($request->branch)) {
                $payslips->where('employees.branch_id', $request->branch);

                $filterYear['branch'] = !empty(Branch::find($request->branch)) ? Branch::find($request->branch)->name : '';
            }

            if (!empty($request->department)) {
                $payslips->where('employees.department_id', $request->department);

                $filterYear['department'] = !empty(Department::find($request->department)) ? Department::find($request->department)->name : '';
            }

            $employees = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->select('id')->get()->pluck('id') : Employee::select('id')->get()->pluck('id');
            $payslips = $branch_id?->isNotEmpty() ? $payslips->whereIn('employee_id', $employees)->get() : $payslips->get();

            $totalBasicSalary = $totalNetSalary = $totalAllowance = $totalCommision = $totalLoan = $totalSaturationDeduction = $totalBpjs = $totalOtherPayment = $totalOverTime = 0;

            foreach ($payslips as $payslip) {
                $totalBasicSalary += $payslip->basic_salary;
                $totalNetSalary += $payslip->net_payble;

                $allowances = json_decode($payslip->allowance);
                foreach ($allowances as $allowance) {
                    $totalAllowance += $allowance->amount;
                }

                $commisions = json_decode($payslip->commission);
                foreach ($commisions as $commision) {
                    $totalCommision += $commision->amount;
                }

                $loans = json_decode($payslip->loan);
                foreach ($loans as $loan) {
                    $totalLoan += $loan->amount;
                }

                $saturationDeductions = json_decode($payslip->saturation_deduction);
                foreach ($saturationDeductions as $saturationDeduction) {
                    $totalSaturationDeduction += $saturationDeduction->amount;
                }

                $bpjs = json_decode($payslip->bpjs) ?? [];
                foreach ($bpjs as $item) {
                    $totalBpjs += $item->amount;
                }

                $otherPayments = json_decode($payslip->other_payment);
                foreach ($otherPayments as $otherPayment) {
                    $totalOtherPayment += $otherPayment->amount;
                }

                $overtimes = json_decode($payslip->overtime);
                foreach ($overtimes as $overtime) {
                    $month = date('m', strtotime($overtime->date));
                    $year = date('Y', strtotime($overtime->date));

                    $employee = Employee::find($overtime->employee_id);
                    $total_work_hours = $employee->getTotalHours($employee->shift_type?->shiftTimes?->where('is_working', 1) ?? collect(), $month, $year);

                    if ($total_work_hours <= 0) {
                        continue;
                    }

                    $total_hours = max(0, round((strtotime($overtime->clock_out) - strtotime($overtime->clock_in)) / 3600, 2));
                    $amount = $overtime->is_work_day ? $total_hours * ($employee->salary / $total_work_hours) : $total_hours * ($employee->salary / $total_work_hours) * 2;
                    $totalOverTime += $amount;
                }
            }

            $filterData['totalBasicSalary'] = $totalBasicSalary;
            $filterData['totalNetSalary'] = $totalNetSalary;
            $filterData['totalAllowance'] = $totalAllowance;
            $filterData['totalCommision'] = $totalCommision;
            $filterData['totalLoan'] = $totalLoan;
            $filterData['totalSaturationDeduction'] = $totalSaturationDeduction;
            $filterData['totalBpjs'] = $totalBpjs;
            $filterData['totalOtherPayment'] = $totalOtherPayment;
            $filterData['totalOverTime'] = $totalOverTime;

            $starting_year = date('Y', strtotime('-5 year'));
            $ending_year = date('Y', strtotime('+5 year'));

            $filterYear['starting_year'] = $starting_year;
            $filterYear['ending_year'] = $ending_year;

            return view('report.payroll', compact('payslips', 'filterData', 'branch', 'department', 'filterYear'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function monthlyAttendance(Request $request)
    {
        if (\Auth::user()->can('Manage Report')) {
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

            $branch = $branch_id?->isNotEmpty() ? Branch::whereIn('id', $branch_id)->get() : Branch::get();

            $department = $branch_id?->isNotEmpty() ? Department::whereIn('branch_id', $branch_id)->get() : Department::get();

            $data['branch'] = __('All');
            $data['department'] = __('All');

            $employees = Employee::orderBy('name', 'ASC');
            if (!empty($request->branch)) {
                $showed_branch = $branch_id?->isNotEmpty() ? Branch::whereIn('id', $branch_id)->find($request->branch) : Branch::find($request->branch);
                if (!empty($showed_branch)) {
                    $data['branch'] = $showed_branch->name;
                }

                $department = $department->where('branch_id', $request->branch);
            }

            if (!empty($request->department)) {
                // $employees->where('department_id', $request->department);
                $showed_department = $branch_id?->isNotEmpty() ? Department::whereIn('branch_id', $branch_id)->find($request->department) : Department::find($request->department);

                if (!empty($showed_department)) {
                    $employees = $employees->where('department_id', $showed_department->id);
                    $data['department'] = $showed_department->name;
                }
            }

            if (!empty($request->month)) {
                $currentdate = strtotime($request->month);
                $month = date('m', $currentdate);
                $year = date('Y', $currentdate);
                $curMonth = date('M-Y', strtotime($request->month));
            } else {
                $month = date('m');
                $year = date('Y');
                $curMonth = date('M-Y', strtotime($year . '-' . $month));
            }

            $startDate = Carbon::createFromFormat('Y-m', $year . '-' . $month)->startOfMonth()->format('Y-m-d');

            $employees = $employees->whereDoesntHave('terminations', function ($query) use ($startDate) {
                // Exclude employees whose termination date is before the start of the given month
                $query->whereDate('termination_date', '<', $startDate);
            })->get();

            $allowedBranchIds = null;
            if (!empty($request->branch)) {
                $allowedBranchIds = [(int) $request->branch];
            } elseif ($branch_id?->isNotEmpty()) {
                $allowedBranchIds = $branch_id->map(function ($id) {
                    return (int) $id;
                })->all();
            }

            $num_of_days = date('t', mktime(0, 0, 0, $month, 1, $year));
            $holiday_date = [];
            for ($i = 1; $i <= $num_of_days; $i++) {
                $formatted_date = str_pad($i, 2, '0', STR_PAD_LEFT);
                $dates[] = $formatted_date;
                $formated_dates[] = $year . '-' . $month . '-' . $formatted_date;
                $date = "{$year}-{$month}-{$formatted_date}";
                $holiday = Holiday::where('start_date', '<=', $date)->where('end_date', '>=', $date)->exists();
                $holiday_date[$date] = $holiday;
            }

            $employeesAttendance = [];
            $totalPresent = $totalLeave = $totalEarlyLeave = 0;
            $totalOvertime = $earlyleaveHours = $earlyleaveMins = $lateHours = $lateMins = 0;
            foreach ($employees as $employee) {
                $attendanceStatus = [];
                $attendances['name'] = $employee->name;
                $hasScopedDay = false;

                $isScoped = function ($dateFormat) use ($allowedBranchIds, $employee) {
                    if ($allowedBranchIds === null) {
                        return true;
                    }

                    return in_array(AttendanceLocationResolver::resolveBranchId($employee->id, $dateFormat), $allowedBranchIds, true);
                };

                $employee_attendances = AttendanceEmployee::where('employee_id', $employee->id)->whereMonth('date', $month)->whereYear('date', $year)->where('is_valid', true)->select('date', 'status', 'early_leaving', 'late', 'shift_type_id')->get()->groupBy('date');
                $employee_overtimes = Overtime::where('employee_id', $employee->id)->whereMonth('date', $month)->whereYear('date', $year)->select('clock_out', 'clock_in', 'date')->get();
                $shift = ShiftTime::where('shift_type_id', $employee->shift_type?->id)->select('is_working', 'days')->get()->pluck('is_working', 'days');

                $rosterByDate = collect();
                if ($employee->is_shift) {
                    $rosterByDate = collect($employee->monthlyShiftSchedule($month, $year));
                }

                //permits table database
                $permits = DB::table('permits')
                    ->where('employee_id', $employee->id)
                    ->whereDate('start_date', '<=', $year . '-' . $month . '-31')
                    ->whereDate('end_date', '>=', $year . '-' . $month . '-01')
                    ->get();

                foreach ($employee_overtimes as $overtime) {
                    if (!$isScoped($overtime->date)) {
                        continue;
                    }

                    $total_hours = max(0, round((strtotime($overtime->clock_out) - strtotime($overtime->clock_in)) / 3600, 2));
                    $totalOvertime += $total_hours;
                }

                foreach ($employee_attendances->flatten(1) as $attendance) {
                    if (!$isScoped($attendance->date)) {
                        continue;
                    }

                    if ($attendance->early_leaving > 0) {
                        $earlyleaveHours += date('h', strtotime($attendance->early_leaving));
                        $earlyleaveMins += date('i', strtotime($attendance->early_leaving));
                    }

                    if ($attendance->late > 0) {
                        $lateHours += date('h', strtotime($attendance->late));
                        $lateMins += date('i', strtotime($attendance->late));
                    }
                }

                foreach ($dates as $date) {
                    $dateFormat = $year . '-' . $month . '-' . $date;

                    if ($dateFormat <= date('Y-m-d')) {
                        $permitStatus = function () use ($permits, $dateFormat) {
                            $permitOnDate = $permits->first(function ($permit) use ($dateFormat) {
                                return $dateFormat >= $permit->start_date
                                    && $dateFormat <= $permit->end_date;
                            });
                            if ($permitOnDate) {
                                return match ((int) $permitOnDate->permit_type_id) {
                                    1 => 'S',
                                    2 => 'IK',
                                    3 => 'CO',
                                    4 => 'EO',
                                    5 => 'PH',
                                    6 => 'L',
                                    default => 'I',
                                };
                            }
                            return 'I';
                        };

                        // Karyawan shift2an: 1 badge per shift yang dijadwalkan di roster hari itu.
                        if ($employee->is_shift) {
                            $slotsToday = $rosterByDate[$dateFormat] ?? collect();
                            $badges = [];

                            if (!$isScoped($dateFormat)) {
                                if ($slotsToday->isEmpty()) {
                                    $badges[] = 'L';
                                } else {
                                    foreach ($slotsToday as $slot) {
                                        $badges[] = 'A';
                                    }
                                }
                                $attendanceStatus[$date] = $badges;
                                continue;
                            }

                            $hasScopedDay = true;
                            $attRows = $employee_attendances[$dateFormat] ?? collect();
                            $dateHasValidLeave = $attRows->contains(fn($r) => $r->status == 'Leave');
                            $leaveCountedForDate = false;

                            if ($slotsToday->isEmpty()) {
                                // Tidak ada shift dijadwalkan hari itu.
                                $badges[] = 'L';
                            } else {
                                foreach ($slotsToday as $slot) {
                                    $match = $attRows->first(function ($r) use ($slot) {
                                        return (int) $r->shift_type_id === (int) $slot->shift_type_id;
                                    });

                                    if ($match) {
                                        if ($match->status == 'Present' || $match->status == 'No Working Hour') {
                                            $badges[] = 'H';
                                            $totalPresent += 1;
                                        } elseif ($match->status == 'Leave') {
                                            $badges[] = 'C';
                                            if (!$leaveCountedForDate) {
                                                $totalLeave += 1;
                                                $leaveCountedForDate = true;
                                            }
                                        } elseif ($match->status == 'Permission') {
                                            $badges[] = $permitStatus();
                                        } else {
                                            $badges[] = 'A';
                                        }
                                    } else {
                                        // Cuti berlaku penuh 1 hari: jika ada row Leave valid di tanggal ini
                                        // (row cuti hanya dibuat untuk shift pertama), semua shift dianggap 'C'.
                                        if ($dateHasValidLeave) {
                                            $badges[] = 'C';
                                            if (!$leaveCountedForDate) {
                                                $totalLeave += 1;
                                                $leaveCountedForDate = true;
                                            }
                                        } else {
                                            $badges[] = 'A';
                                        }
                                    }
                                }
                            }

                            $attendanceStatus[$date] = $badges;
                            continue;
                        }

                        // Non-shift: tetap 1 badge.
                        $isWorkday = !empty($shift[date('l', strtotime($dateFormat))]) && !$holiday_date[$dateFormat];

                        if (!$isScoped($dateFormat)) {
                            $attendanceStatus[$date] = $isWorkday ? 'A' : 'L';
                            continue;
                        }

                        $hasScopedDay = true;

                        $attRows = $employee_attendances[$dateFormat] ?? collect();

                        if ($attRows->isNotEmpty()) {
                            $presentRow = $attRows->first(function ($r) {
                                return $r->status == 'Present' || $r->status == 'No Working Hour';
                            });

                            if ($presentRow) {
                                $attendanceStatus[$date] = 'H';
                                $totalPresent += 1;
                            } elseif ($attRows->contains(fn($r) => $r->status == 'Leave')) {
                                $attendanceStatus[$date] = 'C';
                                $totalLeave += 1;
                            } elseif ($attRows->contains(fn($r) => $r->status == 'Permission')) {
                                $attendanceStatus[$date] = $permitStatus();
                            } else {
                                $attendanceStatus[$date] = 'A';
                            }
                        } elseif (!$isWorkday) {
                            $attendanceStatus[$date] = 'L';
                        } else {
                            $attendanceStatus[$date] = 'A';
                        }
                    } else {
                        $attendanceStatus[$date] = null;
                    }
                }
                $attendances['status'] = $attendanceStatus;

                if ($hasScopedDay) {
                    $employeesAttendance[] = $attendances;
                }
            }

            $totalEarlyleave = $earlyleaveHours + ($earlyleaveMins / 60);
            $totalLate = $lateHours + ($lateMins / 60);

            $data['totalOvertime'] = $totalOvertime;
            $data['totalEarlyLeave'] = $totalEarlyleave;
            $data['totalLate'] = $totalLate;
            $data['totalPresent'] = $totalPresent;
            $data['totalLeave'] = $totalLeave;
            $data['curMonth'] = $curMonth;

            $department = $department->pluck('name', 'id');
            $branch = $branch->pluck('name', 'id');

            if (empty($branch_id)) {
                $branch->prepend('All', '');
                $department->prepend('All', '');
            }

            $branch_count = 2;
            foreach ($branch as $index => $b) {
                if ($b == 'Head Office') {
                    $branch[$index] = '1. ' . $b;
                } else {
                    $branch[$index] = $branch_count . '. ' . __($b);
                    $branch_count += 1;
                }
            }

            return view('report.monthlyAttendance', compact('employeesAttendance', 'branch', 'department', 'dates', 'data'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function timesheet(Request $request)
    {
        if (\Auth::user()->can('Manage Report')) {
            $branch = Branch::get()->pluck('name', 'id');
            $branch->prepend('All', '');

            $department = Department::get()->pluck('name', 'id');
            $department->prepend('All', '');

            $filterYear['branch'] = __('All');
            $filterYear['department'] = __('All');

            $timesheets = TimeSheet::select('time_sheets.*', 'employees.name')->leftjoin('employees', 'time_sheets.employee_id', '=', 'employees.id')->where('time_sheets.created_by', \Auth::user()->creatorId());

            $timesheetFilters = TimeSheet::select('time_sheets.*', 'employees.name')->groupBy('employee_id')->selectRaw('sum(hours) as total')->leftjoin('employees', 'time_sheets.employee_id', '=', 'employees.id')->where('time_sheets.created_by', \Auth::user()->creatorId());

            if (!empty($request->start_date) && !empty($request->end_date)) {
                $timesheets->where('date', '>=', $request->start_date);
                $timesheets->where('date', '<=', $request->end_date);

                $timesheetFilters->where('date', '>=', $request->start_date);
                $timesheetFilters->where('date', '<=', $request->end_date);

                $filterYear['start_date'] = $request->start_date;
                $filterYear['end_date'] = $request->end_date;
            } else {

                $filterYear['start_date'] = date('Y-m-01');
                $filterYear['end_date'] = date('Y-m-t');

                $timesheets->where('date', '>=', $filterYear['start_date']);
                $timesheets->where('date', '<=', $filterYear['end_date']);

                $timesheetFilters->where('date', '>=', $filterYear['start_date']);
                $timesheetFilters->where('date', '<=', $filterYear['end_date']);
            }

            if (!empty($request->branch)) {
                $timesheets->where('branch_id', $request->branch);
                $timesheetFilters->where('branch_id', $request->branch);
                $filterYear['branch'] = !empty(Branch::find($request->branch)) ? Branch::find($request->branch)->name : '';
            }
            if (!empty($request->department)) {
                $timesheets->where('department_id', $request->department);

                $timesheetFilters->where('department_id', $request->department);

                $filterYear['department'] = !empty(Department::find($request->department)) ? Department::find($request->department)->name : '';
            }

            $timesheets = $timesheets->get();

            $timesheetFilters = $timesheetFilters->get();

            $totalHours = 0;
            foreach ($timesheetFilters as $timesheetFilter) {
                $totalHours += $timesheetFilter->hours;
            }
            $filterYear['totalHours'] = $totalHours;
            $filterYear['totalEmployee'] = count($timesheetFilters);

            return view('report.timesheet', compact('timesheets', 'branch', 'department', 'filterYear', 'timesheetFilters'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function LeaveReportExport()
    {
        $name = 'leave_' . date('Y-m-d i:h:s');
        $data = Excel::download(new LeaveReportExport(), $name . '.xlsx');

        return $data;
    }

    public function AccountStatementReportExport(Request $request)
    {
        $name = 'Account Statement_' . date('Y-m-d i:h:s');
        $data = Excel::download(new accountstatementExport(), $name . '.xlsx');

        return $data;
    }

    public function PayrollReportExport(Request $request)
    {
        $name = 'Payroll_' . date('Y-m-d i:h:s');
        $data = Excel::download(new PayrollExport(), $name . '.xlsx');

        return $data;
    }

    public function exportTimeshhetReport(Request $request)
    {
        $name = 'Timesheet_' . date('Y-m-d i:h:s');
        $data = Excel::download(new TimesheetReportExport(), $name . '.xlsx');

        return $data;
    }

    public function exportCsv($filter_month, $branch, $department)
    {

        $data['branch'] = __('All');
        $data['department'] = __('All');

        $employees = Employee::select('id', 'name')->where('created_by', \Auth::user()->creatorId());
        if ($branch != 0) {
            $employees->where('branch_id', $branch);
            $data['branch'] = !empty(Branch::find($branch)) ? Branch::find($branch)->name : '';
        }

        if ($department != 0) {
            $employees->where('department_id', $department);
            $data['department'] = !empty(Department::find($department)) ? Department::find($department)->name : '';
        }

        $employees = $employees->orderby('name', 'asc')->get()->pluck('name', 'id');

        $currentdate = strtotime($filter_month);
        $month = date('m', $currentdate);
        $year = date('Y', $currentdate);
        $data['curMonth'] = date('M-Y', strtotime($filter_month));

        $fileName = $data['branch'] . ' ' . __('Branch') . ' ' . $data['curMonth'] . ' ' . __('Attendance Report of') . ' ' . $data['department'] . ' ' . __('Department') . ' ' . '.csv';

        $num_of_days = date('t', mktime(0, 0, 0, $month, 1, $year));
        for ($i = 1; $i <= $num_of_days; $i++) {
            $dates[] = str_pad($i, 2, '0', STR_PAD_LEFT);
        }

        foreach ($employees as $id => $employee) {
            $attendances['name'] = $employee;

            foreach ($dates as $date) {

                $dateFormat = $year . '-' . $month . '-' . $date;

                if ($dateFormat <= date('Y-m-d')) {
                    $employeeAttendance = AttendanceEmployee::where('employee_id', $id)->where('date', $dateFormat)->first();

                    if (!empty($employeeAttendance) && $employeeAttendance->status == 'Present') {
                        $attendanceStatus[$date] = 'P';
                    } elseif (!empty($employeeAttendance) && $employeeAttendance->status == 'Leave') {
                        $attendanceStatus[$date] = 'A';
                    } else {
                        $attendanceStatus[$date] = '-';
                    }
                } else {
                    $attendanceStatus[$date] = '-';
                }
                $attendances[$date] = $attendanceStatus[$date];
            }

            $employeesAttendance[] = $attendances;
        }

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0",
        );

        $emp = array(
            'employee',
        );

        $columns = array_merge($emp, $dates);

        $callback = function () use ($employeesAttendance, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($employeesAttendance as $attendance) {
                fputcsv($file, str_replace('"', '', array_values($attendance)));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportMonthlyAttendance(Request $request)
    {
        if (\Auth::user()->can('Manage Report')) {
            $urlQuery = parse_url($request->url, PHP_URL_QUERY);
            $queryArray = [];
            if (!empty($urlQuery)) {
                foreach (explode('&', $urlQuery) as $query) {
                    list($key, $value) = explode('=', $query);
                    $queryArray[$key] = $value;
                }
            }

            $name = 'Monthly_Attendance_Employee' . date('Y-m-d H:i:s');
            $data = Excel::download(new MonthlyAttendanceExport(json_encode($queryArray)), $name . '.xlsx');

            return $data;
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
