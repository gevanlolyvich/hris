<?php

namespace App\Http\Controllers;

use App\Models\Allowance;
use App\Models\AllowanceOption;
use App\Models\Bpjs;
use App\Models\BpjsOption;
use App\Models\Branch;
use App\Models\Commission;
use App\Models\DeductionOption;
use App\Models\Employee;
use App\Models\Loan;
use App\Models\LoanOption;
use App\Models\OtherPayment;
use App\Models\Overtime;
use App\Models\PayslipType;
use App\Models\SalaryChangeRequest;
use App\Models\SaturationDeduction;
use App\Models\AttendanceEmployee;
use App\Exports\SalaryDataSheet;
use App\Exports\SalaryTemplateExport;
use App\Imports\SalaryImport;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class SetSalaryController extends Controller
{
    function getTotalWorkdays($employeeWorkdays, $month, $year)
    {
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

    function getPresentDays($attendanceData, $shiftTimes, $type = 'Fixed')
    {
        // Initialize the present days count
        $presentDaysCount = 0;

        // Approved permits (Permission) do not count as valid days.
        $attendanceData = collect($attendanceData)->reject(function ($attendance) {
            return $attendance['status'] == 'Permission';
        });

        // Loop through each attendance entry
        foreach ($attendanceData as $attendance) {
            // Get the day of the week for the attendance date
            $attendanceDayName = date('l', strtotime($attendance['date']));

            // Check if the attendance date is a workday based on shift times
            $shift = collect($shiftTimes)->firstWhere('days', $attendanceDayName);

            if ($shift && $shift['is_working'] && $type == 'Fixed') {
                // Calculate required work hours based on shift

                $startShift = strtotime($shift['start_time']);
                $endShift = strtotime($shift['end_time']);

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
        if (\Auth::user()->can('Manage Set Salary')) {
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
            $employees = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->where('is_active', 1)->orderby('name', 'asc')->get() : Employee::where('is_active', 1)->orderby('name', 'asc')->get();

            return view('setsalary.index', compact('employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit($id)
    {
        if (\Auth::user()->can('Edit Set Salary')) {
            $payslip_type = PayslipType::get()->pluck('name', 'id');
            $allowance_options = AllowanceOption::get()->pluck('name', 'id');
            $loan_options = LoanOption::get()->pluck('name', 'id');
            $deduction_options = DeductionOption::get()->pluck('name', 'id');
            $bpjs_options = BpjsOption::get()->pluck('name', 'id');
            if (\Auth::user()->type == 'employee') {
                $currentEmployee = Employee::where('is_active', 1)->where('user_id', '=', \Auth::user()->id)->first();

                if (empty($currentEmployee) || !$currentEmployee) {
                    return redirect()->back()->with('error', __('Inactive'));
                }

                $allowances = Allowance::where('employee_id', $currentEmployee->id)->get();
                $commissions = Commission::where('employee_id', $currentEmployee->id)->get();
                $loans = Loan::where('employee_id', $currentEmployee->id)->get();
                $saturationdeductions = SaturationDeduction::where('employee_id', $currentEmployee->id)->get();
                $otherpayments = OtherPayment::where('employee_id', $currentEmployee->id)->get();
                $overtimes = Overtime::where('employee_id', $currentEmployee->id)->get();
                $bpjs = Bpjs::where('employee_id', $currentEmployee->id)->get();
                $employee = Employee::where('user_id', '=', \Auth::user()->id)->first();

                return view('setsalary.employee_salary', compact('employee', 'payslip_type', 'allowance_options', 'commissions', 'loan_options', 'overtimes', 'otherpayments', 'saturationdeductions', 'loans', 'deduction_options', 'allowances', 'bpjs', 'bpjs_options'));

            } else {
                $allowances = Allowance::where('employee_id', $id)->get();
                $commissions = Commission::where('employee_id', $id)->get();
                $loans = Loan::where('employee_id', $id)->get();
                $saturationdeductions = SaturationDeduction::where('employee_id', $id)->get();
                $otherpayments = OtherPayment::where('employee_id', $id)->get();
                $overtimes = Overtime::where('employee_id', $id)->get();
                $bpjs = Bpjs::where('employee_id', $id)->get();
                $employee = Employee::where('is_active', 1)->find($id);

                return view('setsalary.edit', compact('employee', 'payslip_type', 'allowance_options', 'commissions', 'loan_options', 'overtimes', 'otherpayments', 'saturationdeductions', 'loans', 'deduction_options', 'allowances', 'bpjs', 'bpjs_options'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show($id, Request $request)
    {
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

        $year = $request->month ? date('Y', strtotime($request->month)) : date('Y');
        $month = $request->month ? date('m', strtotime($request->month)) : date('m');
        $start_date = date($year . '-' . $month . '-01');
        $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

        $payslip_type = PayslipType::get()->pluck('name', 'id');
        $allowance_options = AllowanceOption::get()->pluck('name', 'id');
        $loan_options = LoanOption::get()->pluck('name', 'id');
        $deduction_options = DeductionOption::get()->pluck('name', 'id');
        $bpjs_options = BpjsOption::get()->pluck('name', 'id');

        $employee = null;
        if (\Auth::user()->type == 'employee') {
            $employee = Employee::find(\Auth::user()->id);
        } else {
            $employee = $branch_id?->isNotEmpty() ? Employee::where('id', $id)->whereIn('branch_id', $branch_id)->first() : Employee::find($id);
        }

        if (empty($employee)) {
            return redirect()->back()->with('error', __('Permission denied'));
        }

        $allowances = Allowance::where('employee_id', $employee->id)->where(function ($query) use ($month, $year) {
            $query->orWhere('is_recurring', true)
                ->orWhere('period', "{$year}-{$month}");
        })->get();
        $commissions = Commission::where('employee_id', $employee->id)->where(function ($query) use ($month, $year) {
            $query->orWhere('is_recurring', true)
                ->orWhere('period', "{$year}-{$month}");
        })->get();
        $current = "{$year}-{$month}";
        $loans = Loan::where('employee_id', $employee->id)->where(function ($query) use ($current) {
            $query->where(function ($q) use ($current) {
                $q->where('is_recurring', true)
                    ->where('period_start', '<=', $current)
                    ->where('period_end', '>=', $current);
            })->orWhere('period', $current);
        })->get();
        $saturationdeductions = SaturationDeduction::where('employee_id', $employee->id)->where(function ($query) use ($month, $year) {
            $query->orWhere('is_recurring', true)
                ->orWhere('period', "{$year}-{$month}");
        })->get();
        $otherpayments = OtherPayment::where('employee_id', $employee->id)->where(function ($query) use ($month, $year) {
            $query->orWhere('is_recurring', true)
                ->orWhere('period', "{$year}-{$month}");
        })->get();
        $bpjs = Bpjs::where('employee_id', $employee->id)->get();
        $overtimes = Overtime::where('employee_id', $employee->id)->whereMonth('date', $month)->whereYear('date', $year)->whereNotNull('report_document')->where('status', 'approved')->get();

        list($total_work_days) = $employee->salaryWorkdaysAndPresentDays($month, $year);
        // $total_work_hours = (new Employee)->getTotalHours($employee->shift_type->shiftTimes->where('is_working', 1), $month, $year);
        $total_work_hours = 173; // Standard working hours in a month (e.g., 8 hours/day * 21.625 workdays)
        $total_present_days = $employee->getPresentDays(AttendanceEmployee::where('employee_id', $employee->id)->whereMonth('date', $month)->whereYear('date', $year)->where('is_valid', 1)->select('date', 'status', 'work_hours', 'is_valid', 'shift_type_id')->get(), $employee->shift_type?->shiftTimes->where('is_working', 1) ?? collect(), $employee->employeeType?->type);

        $fixed_rate = $employee->payrollRate($total_present_days, $total_work_days);
        $total_allowance = 0;
        foreach ($allowances as $value) {
            $empsal = $value->type == 'percentage' ? $value->amount * $employee->salary / 100 : $value->amount;
            if ($value->is_prorated) {
                $empsal = $employee->employeeType->type == 'Fixed' ? $empsal * $fixed_rate : $empsal * $total_present_days;
            }
            $value->tota_allow = $empsal;
            $value->effective_amount = $empsal;
            $total_allowance += $empsal;
        }

        foreach ($commissions as $value) {
            if ($value->type == 'percentage') {
                $empsal = $value->amount * $employee->salary / 100;
                $value->tota_allow = $empsal;
            }
        }

        foreach ($loans as $value) {
            if ($value->type == 'percentage') {
                // $employee          = Employee::find($value->employee_id);
                $empsal = $value->amount * $employee->salary / 100;
                $value->tota_allow = $empsal;
            }
        }

        foreach ($saturationdeductions as $value) {
            if ($value->type == 'percentage') {
                // $employee          = Employee::find($value->employee_id);
                $empsal = $value->amount * $employee->salary / 100;
                $value->tota_allow = $empsal;
            }
        }

        foreach ($otherpayments as $value) {
            if ($value->type == 'percentage') {
                // $employee          = Employee::find($value->employee_id);
                $empsal = $value->amount * $employee->salary / 100;
                $value->tota_allow = $empsal;
            }
        }

        foreach ($bpjs as $value) {
            $value->tota_allow = $value->resolvedAmount($employee, $fixed_rate, $total_present_days);
        }

        return view('setsalary.employee_salary', compact('employee', 'payslip_type', 'allowance_options', 'commissions', 'loan_options', 'overtimes', 'otherpayments', 'saturationdeductions', 'loans', 'deduction_options', 'allowances', 'total_work_days', 'total_work_hours', 'total_present_days', 'total_allowance', 'bpjs', 'bpjs_options'));
    }


    public function employeeUpdateSalary(Request $request, $id)
    {
        if (! \Auth::user()->can('Edit Set Salary')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'salary_type' => 'required',
                'salary' => 'required|numeric|min:0',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }
        $employee = Employee::findOrFail($id);
        $newSalary = (float) $request->input('salary');

        // Payslip type is a label, not a monetary value, so it applies at once.
        $employee->salary_type = $request->input('salary_type');

        if (self::requiresSalaryApproval($employee, $newSalary)) {
            $this->createSalaryChangeRequest($employee, $newSalary, SalaryChangeRequest::SOURCE_FORM);

            return redirect()->back()->with('success', __('Salary change submitted for approval.'));
        }

        $employee->salary = $newSalary;
        $employee->save();

        return redirect()->back()->with('success', 'Employee Salary Updated.');
    }

    /**
     * An employee with no salary yet gets one straight away. Once a salary is
     * on record every later change waits for the designated reviewer.
     */
    private static function requiresSalaryApproval($employee, $newSalary)
    {
        $current = (float) $employee->salary;

        if ($current <= 0) {
            return false;
        }

        return abs($current - $newSalary) > 0.0000001;
    }

    private function createSalaryChangeRequest($employee, $newSalary, $source)
    {
        $existing = SalaryChangeRequest::pending()
            ->where('employee_id', $employee->id)
            ->first();

        if ($existing) {
            $existing->update([
                'old_salary' => (float) $employee->salary,
                'new_salary' => $newSalary,
                'source' => $source,
                'requested_by' => \Auth::id(),
            ]);

            return $existing;
        }

        return SalaryChangeRequest::create([
            'employee_id' => $employee->id,
            'old_salary' => (float) $employee->salary,
            'new_salary' => $newSalary,
            'source' => $source,
            'status' => SalaryChangeRequest::STATUS_PENDING,
            'requested_by' => \Auth::id(),
        ]);
    }

    public function employeeSalary()
    {
        if (\Auth::user()->type == "employee") {
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

            $employees = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->where('user_id', \Auth::user()->id)->orderby('name', 'asc')->get() : Employee::where('user_id', \Auth::user()->id)->orderby('name', 'asc')->get();

            return view('setsalary.index', compact('employees'));
        }
    }

    public function employeeBasicSalary($id)
    {

        $payslip_type = PayslipType::get()->pluck('name', 'id');
        $employee = Employee::find($id);

        return view('setsalary.basic_salary', compact('employee', 'payslip_type'));
    }

    public function importFile()
    {
        return view('setsalary.import');
    }

    public function exportTemplate()
    {
        $name = 'salary-template-' . date('Y-m-d_H-i-s');

        return Excel::download(new SalaryTemplateExport(), $name . '.xlsx');
    }

    public function import(Request $request)
    {
        $rules = [
            'file' => 'required|mimes:csv,txt,xlsx',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $rows = (new SalaryImport())->toArray(request()->file('file'))[0];
        $totalRecord = count($rows) - 1;
        $errorArray = [];
        $pendingSalaryCount = 0;

        for ($i = 1; $i <= count($rows) - 1; $i++) {
            $row = $rows[$i];

            $employeeId = $this->val($row, SalaryDataSheet::COL_EMPLOYEE_ID);
            $salary = $this->val($row, SalaryDataSheet::COL_SALARY);
            $salaryTypeName = $this->val($row, SalaryDataSheet::COL_SALARY_TYPE);

            // Baris benar-benar kosong -> lewati.
            if ($this->rowIsEmpty($row)) {
                continue;
            }

            if (empty($employeeId)) {
                $errorArray[] = $this->withReason($row, __('Employee Id kosong'));
                continue;
            }

            $employee = Employee::where('employee_id', $employeeId)->where('is_active', 1)->first();

            if (empty($employee)) {
                $errorArray[] = $this->withReason($row, __('Karyawan tidak ditemukan / tidak aktif'));
                continue;
            }

            $errors = [];
            $components = [];

            // Siapkan data komponen (validasi dahulu sebelum menyimpan apapun).
            $allowance = $this->prepareAllowance($employee, $row);
            if ($allowance['ok'] === false) {
                $errors[] = $allowance['error'];
            } elseif (!is_null($allowance['data'])) {
                $components['allowance'] = $allowance['data'];
            }

            $commission = $this->prepareCommission($employee, $row);
            if ($commission['ok'] === false) {
                $errors[] = $commission['error'];
            } elseif (!is_null($commission['data'])) {
                $components['commission'] = $commission['data'];
            }

            $otherPayment = $this->prepareOtherPayment($employee, $row);
            if ($otherPayment['ok'] === false) {
                $errors[] = $otherPayment['error'];
            } elseif (!is_null($otherPayment['data'])) {
                $components['other_payment'] = $otherPayment['data'];
            }

            $loan = $this->prepareLoan($employee, $row);
            if ($loan['ok'] === false) {
                $errors[] = $loan['error'];
            } elseif (!is_null($loan['data'])) {
                $components['loan'] = $loan['data'];
            }

            $bpjs = $this->prepareBpjs($employee, $row);
            if ($bpjs['ok'] === false) {
                $errors[] = $bpjs['error'];
            } elseif (!is_null($bpjs['data'])) {
                $components['bpjs'] = $bpjs['data'];
            }

            $deduction = $this->prepareDeduction($employee, $row);
            if ($deduction['ok'] === false) {
                $errors[] = $deduction['error'];
            } elseif (!is_null($deduction['data'])) {
                $components['deduction'] = $deduction['data'];
            }

            // Jika ada kesalahan validasi komponen, seluruh baris dianggap gagal.
            if (!empty($errors)) {
                $errorArray[] = $this->withReason($row, implode('; ', $errors));
                continue;
            }

            // Baris dengan Salary -> update salary (wajib numerik).
            if ($salary !== '') {
                if (!is_numeric($salary)) {
                    $errorArray[] = $this->withReason($row, __('Salary harus berupa angka'));
                    continue;
                }

                $newSalary = (float) $salary;

                if (!empty($salaryTypeName)) {
                    $payslipType = PayslipType::where('name', $salaryTypeName)->first();

                    if (empty($payslipType)) {
                        $errorArray[] = $this->withReason($row, __('Salary Type tidak dikenal: ') . $salaryTypeName);
                        continue;
                    }

                    // Label only, so it applies without waiting for the reviewer.
                    $employee->salary_type = $payslipType->id;
                }

                if (self::requiresSalaryApproval($employee, $newSalary)) {
                    $this->createSalaryChangeRequest($employee, $newSalary, SalaryChangeRequest::SOURCE_IMPORT);
                    $pendingSalaryCount++;
                } else {
                    $employee->salary = $newSalary;
                    $employee->save();
                }
            } elseif (empty($components)) {
                // Tidak ada salary dan tidak ada komponen yang diisi.
                $errorArray[] = $this->withReason($row, __('Tidak ada data yang diisi (Salary kosong)'));
                continue;
            }

            // Simpan komponen (upsert).
            foreach ($components as $component) {
                $this->upsertRecord($component['model'], $component['criteria'], $component['values']);
            }
        }

        $errorRecord = [];
        if (empty($errorArray)) {
            $data['status'] = 'success';
            $data['msg']    = $pendingSalaryCount > 0
                ? __('Record successfully imported') . ' — ' . $pendingSalaryCount . ' ' . __('salary change submitted for approval.')
                : __('Record successfully imported');
        } else {
            $data['status'] = 'error';
            $data['msg']    = count($errorArray) . ' ' . __('Record imported fail out of' . ' ' . $totalRecord . ' ' . 'record');

            if ($pendingSalaryCount > 0) {
                $data['msg'] .= ' — ' . $pendingSalaryCount . ' ' . __('salary change submitted for approval.');
            }

            foreach ($errorArray as $errorData) {
                $errorRecord[] = implode(',', $errorData);
            }

            \Session::put('errorArray', $errorRecord);
        }

        return redirect()->back()->with($data['status'], $data['msg']);
    }

    private function val(array $row, int $col): string
    {
        return trim((string)($row[$col] ?? ''));
    }

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && $value !== '') {
                return false;
            }
        }

        return true;
    }

    private function withReason(array $row, string $reason): array
    {
        $row[] = $reason;

        return $row;
    }

    private function upsertRecord(string $model, array $criteria, array $values): void
    {
        $record = $model::where($criteria)->first();

        if ($record) {
            $record->update($values);
        } else {
            $model::create(array_merge($criteria, $values));
        }
    }

    private function normalizeType(string $value, string $default): ?string
    {
        $value = strtolower(trim($value));

        if ($value === '') {
            return $default;
        }

        return in_array($value, ['fixed', 'percentage']) ? $value : null;
    }

    private function validatePeriod(string $value, string $label): bool
    {
        if ($value === '') {
            return false;
        }

        return (bool) preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value);
    }

    private function resolveOption(string $model, string $name): array
    {
        if ($name !== '') {
            $option = $model::where('name', $name)->orderBy('id', 'asc')->first();

            if (empty($option)) {
                return ['ok' => false, 'error' => __('Pilihan "') . $name . __('" tidak ditemukan')];
            }

            return ['ok' => true, 'id' => $option->id];
        }

        $option = $model::orderBy('id', 'asc')->first();

        if (empty($option)) {
            return ['ok' => false, 'error' => __('Belum ada pilihan yang tersedia')];
        }

        return ['ok' => true, 'id' => $option->id];
    }

    private function prepareAllowance(Employee $employee, array $row): array
    {
        $amount = $this->val($row, SalaryDataSheet::COL_ALLOWANCE_AMOUNT);

        if ($amount === '') {
            return ['ok' => true, 'data' => null];
        }

        if (!is_numeric($amount)) {
            return ['ok' => false, 'error' => __('Allowance Amount harus berupa angka')];
        }

        $recurringRaw = $this->val($row, SalaryDataSheet::COL_ALLOWANCE_RECURRING);
        $recurring = $recurringRaw === '' ? 1 : (int) $recurringRaw;

        if (!in_array($recurring, [0, 1, 2], true)) {
            return ['ok' => false, 'error' => __('Allowance Recurring harus 0, 1, atau 2')];
        }

        $period = null;
        if ($recurring === 0) {
            $period = $this->val($row, SalaryDataSheet::COL_ALLOWANCE_PERIOD);

            if (!$this->validatePeriod($period, 'Allowance Period')) {
                return ['ok' => false, 'error' => __('Allowance Period wajib diisi format YYYY-MM (contoh: 2026-08)')];
            }
        }

        $option = $this->resolveOption(AllowanceOption::class, $this->val($row, SalaryDataSheet::COL_ALLOWANCE_OPTION));
        if ($option['ok'] === false) {
            return $option;
        }

        $title = $this->val($row, SalaryDataSheet::COL_ALLOWANCE_TITLE);
        if ($title === '') {
            $title = AllowanceOption::find($option['id'])->name;
        }

        $isRecurring = $recurring === 2 ? 1 : $recurring;
        $isProrated = $recurring === 2 ? 1 : 0;

        return [
            'ok' => true,
            'data' => [
                'model' => Allowance::class,
                'criteria' => [
                    'employee_id' => $employee->id,
                    'allowance_option' => $option['id'],
                    'is_recurring' => $isRecurring,
                    'is_prorated' => $isProrated,
                    'period' => $period,
                    'title' => $title,
                ],
                'values' => [
                    'amount' => $amount,
                    'type' => 'fixed',
                    'created_by' => \Auth::user()->id,
                ],
            ],
        ];
    }

    private function prepareCommission(Employee $employee, array $row): array
    {
        return $this->prepareTitleTypeComponent(
            $employee,
            $row,
            Commission::class,
            SalaryDataSheet::COL_COMMISSION_TITLE,
            SalaryDataSheet::COL_COMMISSION_TYPE,
            SalaryDataSheet::COL_COMMISSION_RECURRING,
            SalaryDataSheet::COL_COMMISSION_PERIOD,
            SalaryDataSheet::COL_COMMISSION_AMOUNT,
            'Commission',
            'Commission Type',
            'Commission Recurring',
            'Commission Period',
            'Commission Amount'
        );
    }

    private function prepareOtherPayment(Employee $employee, array $row): array
    {
        return $this->prepareTitleTypeComponent(
            $employee,
            $row,
            OtherPayment::class,
            SalaryDataSheet::COL_OTHERPAYMENT_TITLE,
            SalaryDataSheet::COL_OTHERPAYMENT_TYPE,
            SalaryDataSheet::COL_OTHERPAYMENT_RECURRING,
            SalaryDataSheet::COL_OTHERPAYMENT_PERIOD,
            SalaryDataSheet::COL_OTHERPAYMENT_AMOUNT,
            'Other Payment',
            'Other Payment Type',
            'Other Payment Recurring',
            'Other Payment Period',
            'Other Payment Amount'
        );
    }

    private function prepareTitleTypeComponent(
        Employee $employee,
        array $row,
        string $model,
        int $colTitle,
        int $colType,
        int $colRecurring,
        int $colPeriod,
        int $colAmount,
        string $defaultTitle,
        string $labelType,
        string $labelRecurring,
        string $labelPeriod,
        string $labelAmount
    ): array {
        $amount = $this->val($row, $colAmount);

        if ($amount === '') {
            return ['ok' => true, 'data' => null];
        }

        if (!is_numeric($amount)) {
            return ['ok' => false, 'error' => __($labelAmount . ' harus berupa angka')];
        }

        $type = $this->normalizeType($this->val($row, $colType), 'fixed');
        if (is_null($type)) {
            return ['ok' => false, 'error' => __($labelType . ' harus fixed atau percentage')];
        }

        $recurringRaw = $this->val($row, $colRecurring);
        $recurring = $recurringRaw === '' ? 1 : (int) $recurringRaw;

        if (!in_array($recurring, [0, 1], true)) {
            return ['ok' => false, 'error' => __($labelRecurring . ' harus 0 atau 1')];
        }

        $period = null;
        if ($recurring === 0) {
            $period = $this->val($row, $colPeriod);

            if (!$this->validatePeriod($period, $labelPeriod)) {
                return ['ok' => false, 'error' => __($labelPeriod . ' wajib diisi format YYYY-MM (contoh: 2026-08)')];
            }
        }

        $title = $this->val($row, $colTitle);
        if ($title === '') {
            $title = $defaultTitle;
        }

        return [
            'ok' => true,
            'data' => [
                'model' => $model,
                'criteria' => [
                    'employee_id' => $employee->id,
                    'title' => $title,
                    'type' => $type,
                    'is_recurring' => $recurring,
                    'period' => $period,
                ],
                'values' => [
                    'amount' => $amount,
                    'created_by' => \Auth::user()->id,
                ],
            ],
        ];
    }

    private function prepareLoan(Employee $employee, array $row): array
    {
        $amount = $this->val($row, SalaryDataSheet::COL_LOAN_AMOUNT);

        if ($amount === '') {
            return ['ok' => true, 'data' => null];
        }

        if (!is_numeric($amount)) {
            return ['ok' => false, 'error' => __('Loan Amount harus berupa angka')];
        }

        $type = $this->normalizeType($this->val($row, SalaryDataSheet::COL_LOAN_TYPE), 'fixed');
        if (is_null($type)) {
            return ['ok' => false, 'error' => __('Loan Type harus fixed atau percentage')];
        }

        $recurringRaw = $this->val($row, SalaryDataSheet::COL_LOAN_RECURRING);
        $recurring = $recurringRaw === '' ? 1 : (int) $recurringRaw;

        if (!in_array($recurring, [0, 1], true)) {
            return ['ok' => false, 'error' => __('Loan Recurring harus 0 atau 1')];
        }

        $period = null;
        $periodStart = null;
        $periodEnd = null;

        if ($recurring === 0) {
            $period = $this->val($row, SalaryDataSheet::COL_LOAN_PERIOD);

            if (!$this->validatePeriod($period, 'Loan Period')) {
                return ['ok' => false, 'error' => __('Loan Period wajib diisi format YYYY-MM (contoh: 2026-08)')];
            }
        } else {
            $periodStart = $this->val($row, SalaryDataSheet::COL_LOAN_PERIOD_START);
            $periodEnd = $this->val($row, SalaryDataSheet::COL_LOAN_PERIOD_END);

            if (!$this->validatePeriod($periodStart, 'Loan Period Start')) {
                return ['ok' => false, 'error' => __('Loan Period Start wajib diisi format YYYY-MM untuk pinjaman berulang')];
            }

            if (!$this->validatePeriod($periodEnd, 'Loan Period End')) {
                return ['ok' => false, 'error' => __('Loan Period End wajib diisi format YYYY-MM untuk pinjaman berulang')];
            }
        }

        $option = $this->resolveOption(LoanOption::class, $this->val($row, SalaryDataSheet::COL_LOAN_OPTION));
        if ($option['ok'] === false) {
            return $option;
        }

        $title = $this->val($row, SalaryDataSheet::COL_LOAN_TITLE);
        if ($title === '') {
            $title = LoanOption::find($option['id'])->name;
        }

        return [
            'ok' => true,
            'data' => [
                'model' => Loan::class,
                'criteria' => [
                    'employee_id' => $employee->id,
                    'loan_option' => $option['id'],
                    'is_recurring' => $recurring,
                    'period' => $period,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'title' => $title,
                ],
                'values' => [
                    'type' => $type,
                    'amount' => $amount,
                    'reason' => $this->val($row, SalaryDataSheet::COL_LOAN_REASON) ?: '-',
                    'created_by' => \Auth::user()->id,
                ],
            ],
        ];
    }

    private function prepareBpjs(Employee $employee, array $row): array
    {
        $amount = $this->val($row, SalaryDataSheet::COL_BPJS_AMOUNT);

        if ($amount === '') {
            return ['ok' => true, 'data' => null];
        }

        if (!is_numeric($amount)) {
            return ['ok' => false, 'error' => __('BPJS Amount harus berupa angka')];
        }

        $type = $this->normalizeType($this->val($row, SalaryDataSheet::COL_BPJS_TYPE), 'percentage');
        if (is_null($type)) {
            return ['ok' => false, 'error' => __('BPJS Type harus percentage')];
        }

        $recurringRaw = $this->val($row, SalaryDataSheet::COL_BPJS_RECURRING);
        $recurring = $recurringRaw === '' ? 1 : (int) $recurringRaw;

        if (!in_array($recurring, [0, 1, 2], true)) {
            return ['ok' => false, 'error' => __('BPJS Recurring harus 0, 1, atau 2')];
        }

        $option = $this->resolveOption(BpjsOption::class, $this->val($row, SalaryDataSheet::COL_BPJS_OPTION));
        if ($option['ok'] === false) {
            return $option;
        }

        $isRecurring = $recurring === 2 ? 1 : $recurring;
        $isProrated = $recurring === 2 ? 1 : 0;

        return [
            'ok' => true,
            'data' => [
                'model' => Bpjs::class,
                'criteria' => [
                    'employee_id' => $employee->id,
                    'bpjs_option' => $option['id'],
                    'is_recurring' => $isRecurring,
                    'is_prorated' => $isProrated,
                ],
                'values' => [
                    'type' => $type,
                    'amount' => $amount,
                    'created_by' => \Auth::user()->id,
                ],
            ],
        ];
    }

    private function prepareDeduction(Employee $employee, array $row): array
    {
        $amount = $this->val($row, SalaryDataSheet::COL_DEDUCTION_AMOUNT);

        if ($amount === '') {
            return ['ok' => true, 'data' => null];
        }

        if (!is_numeric($amount)) {
            return ['ok' => false, 'error' => __('Deduction Amount harus berupa angka')];
        }

        $type = $this->normalizeType($this->val($row, SalaryDataSheet::COL_DEDUCTION_TYPE), 'fixed');
        if (is_null($type)) {
            return ['ok' => false, 'error' => __('Deduction Type harus fixed atau percentage')];
        }

        $recurringRaw = $this->val($row, SalaryDataSheet::COL_DEDUCTION_RECURRING);
        $recurring = $recurringRaw === '' ? 1 : (int) $recurringRaw;

        if (!in_array($recurring, [0, 1], true)) {
            return ['ok' => false, 'error' => __('Deduction Recurring harus 0 atau 1')];
        }

        $period = null;
        if ($recurring === 0) {
            $period = $this->val($row, SalaryDataSheet::COL_DEDUCTION_PERIOD);

            if (!$this->validatePeriod($period, 'Deduction Period')) {
                return ['ok' => false, 'error' => __('Deduction Period wajib diisi format YYYY-MM (contoh: 2026-08)')];
            }
        }

        $option = $this->resolveOption(DeductionOption::class, $this->val($row, SalaryDataSheet::COL_DEDUCTION_OPTION));
        if ($option['ok'] === false) {
            return $option;
        }

        $title = $this->val($row, SalaryDataSheet::COL_DEDUCTION_TITLE);
        if ($title === '') {
            $title = DeductionOption::find($option['id'])->name;
        }

        return [
            'ok' => true,
            'data' => [
                'model' => SaturationDeduction::class,
                'criteria' => [
                    'employee_id' => $employee->id,
                    'deduction_option' => $option['id'],
                    'is_recurring' => $recurring,
                    'period' => $period,
                    'title' => $title,
                ],
                'values' => [
                    'type' => $type,
                    'amount' => $amount,
                    'created_by' => \Auth::user()->id,
                ],
            ],
        ];
    }
}
