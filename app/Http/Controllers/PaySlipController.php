<?php

namespace App\Http\Controllers;

use App\Exports\PayslipExport;
use App\Models\Allowance;
use App\Models\Branch;
use App\Models\Commission;
use App\Models\Employee;
use App\Models\DeductionOption;
use App\Models\Loan;
use App\Mail\InvoiceSend;
use App\Mail\PayslipSend;
use App\Models\OtherPayment;
use App\Models\Overtime;
use App\Models\PaySlip;
use App\Models\Pph21;
use App\Models\SaturationDeduction;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class PaySlipController extends Controller
{

    public function index(Request $request)
    {
        $month = $request->month ?? date('Y-m', strtotime(date('Y-m') . ' -1 month'));
        if (\Auth::user()->can('Manage Pay Slip') && \Auth::user()->type != 'employee') {
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

            if ($request->branch) {
                $employees = Employee::where('branch_id', $request->branch)->select('id')->get()->pluck('id');
                $payslips = PaySlip::whereIn('employee_id', $employees)->where('salary_month', $month)->withAggregate('employees', 'name')->orderBy('employees_name', 'asc')->get();
            } elseif ($branch_id?->isNotEmpty()) {
                $employees = Employee::whereIn('branch_id', $branch_id)->select('id')->get()->pluck('id');
                $payslips = PaySlip::whereIn('employee_id', $employees)->where('salary_month', $month)->withAggregate('employees', 'name')->orderBy('employees_name', 'asc')->get();
            } else {
                $payslips = PaySlip::where('salary_month', $month)->withAggregate('employees', 'name')->orderBy('employees_name', 'asc')->get();
            }

            $branch = $branch_id?->isNotEmpty() ? Branch::whereIn('id', $branch_id)->get()->pluck('name', 'id') : Branch::get()->pluck('name', 'id');

            return view('payslip.index', compact('payslips', 'month', 'branch'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function indexEmployee($id, Request $request)
    {
        $month = $request->month;
        $id = Crypt::decrypt($id);
        if (\Auth::user()->can('Manage Pay Slip') && (\Auth::user()?->employee?->id == $id || \Auth::user()->type != 'employee')) {
            $payslips = null;

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

            if (!empty($month)) {
                $payslips = $branch_id?->isNotEmpty() ? PaySlip::whereHas('employees', function ($query) use ($branch_id) {
                    $query->whereIn('branch_id', $branch_id);
                })->where('salary_month', $month)->where('employee_id', $id)->orderBy('salary_month', 'DESC')->get() : PaySlip::where('salary_month', $month)->where('employee_id', $id)->orderBy('salary_month', 'DESC')->get();
            } else {
                $payslips = $branch_id?->isNotEmpty() ? PaySlip::whereHas('employees', function ($query) use ($branch_id) {
                    $query->whereIn('branch_id', $branch_id);
                })->where('employee_id', $id)->orderBy('salary_month', 'DESC')->get() : PaySlip::where('employee_id', $id)->orderBy('salary_month', 'DESC')->get();
            }

            return view('payslip.employee', compact('payslips', 'month'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'month' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $formate_month_year = $request->month ?? date('Y-m');
        $month = date('m', strtotime($request->month));
        $year = date('Y', strtotime($request->month));

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

        if ($request->branch) {
            $validatePaysilp = PaySlip::whereHas('employees', function ($query) use ($request) {
                $query->where('branch_id', $request->branch);
            })->where('salary_month', $formate_month_year)->pluck('employee_id');
            $payslip_employee = Employee::where('branch_id', $request->branch)->where('is_active', 1)->where('company_doj', '<=', date($year . '-' . $month . '-t'))->count();
        } elseif (!$request->branch && \Auth::user()->branch_id) {
            $validatePaysilp = PaySlip::whereHas('employees', function ($query) use ($request) {
                $query->where('branch_id', \Auth::user()->branch_id);
            })->where('salary_month', $formate_month_year)->pluck('employee_id');
            $payslip_employee = Employee::where('branch_id', \Auth::user()->branch_id)->where('is_active', 1)->where('company_doj', '<=', date($year . '-' . $month . '-t'))->count();
        } else {
            $validatePaysilp = $branch_id?->isNotEmpty() ? PaySlip::whereHas('employees', function ($query) use ($branch_id) {
                $query->whereIn('branch_id', $branch_id);
            })->where('salary_month', $formate_month_year)->pluck('employee_id') : PaySlip::where('salary_month', $formate_month_year)->pluck('employee_id');
            $payslip_employee = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->where('is_active', 1)->where('company_doj', '<=', date($year . '-' . $month . '-t'))->count() : Employee::where('is_active', 1)->where('company_doj', '<=', date($year . '-' . $month . '-t'))->count();
        }

        if ($payslip_employee > count($validatePaysilp)) {
            if ($request->branch) {
                $employees = Employee::where('branch_id', $request->branch)->where('is_active', 1)->where('company_doj', '<=', date($year . '-' . $month . '-t'))->whereNotIn('employee_id', $validatePaysilp)->get();

                // check if there is employe that salary has to be set
                $employeesSalary = Employee::where('branch_id', $request->branch)->where('is_active', 1)->where('salary', '>', 0)->first();
            } else {
                $employees = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->where('is_active', 1)->where('company_doj', '<=', date($year . '-' . $month . '-t'))->whereNotIn('employee_id', $validatePaysilp)->get() : Employee::where('is_active', 1)->where('company_doj', '<=', date($year . '-' . $month . '-t'))->whereNotIn('employee_id', $validatePaysilp)->get();

                // check if there is employe that salary has to be set
                $employeesSalary = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->where('is_active', 1)->where('salary', '>', 0)->first() : Employee::where('is_active', 1)->where('salary', '>', 0)->first();
            }


            // if (!empty($employeesSalary)) {
            //     return redirect()->back()->with('error', __('Please set employee salary.'));
            // }

            $employees = $employees->whereNotIn('id', $validatePaysilp);

            foreach ($employees as $employee) {
                $payslipEmployee = new PaySlip();
                $payslipEmployee->employee_id = $employee->id;
                $payslipEmployee->bruto = $employee->get_bruto_salary($month, $year);
                $payslipEmployee->net_payble = $employee->get_net_salary($month, $year);
                $payslipEmployee->salary_month = $formate_month_year;
                $payslipEmployee->status = 0; // not paid yet
                $payslipEmployee->basic_salary = $employee->get_salary($month, $year);
                $payslipEmployee->allowance = Employee::allowance($employee->id, $month, $year);
                $payslipEmployee->commission = Employee::commission($employee->id, $month, $year);
                $payslipEmployee->loan = Employee::loan($employee->id, $month, $year);
                $payslipEmployee->saturation_deduction = Employee::saturation_deduction($employee->id, $month, $year);
                $payslipEmployee->other_payment = Employee::other_payment($employee->id, $month, $year);
                $payslipEmployee->overtime = Employee::get_overtime($employee->id, $month, $year);
                $payslipEmployee->created_by = \Auth::user()->id;

                $payslipEmployee->save();

                // slack 
                $setting = Utility::settings();
                $monthYear = date('M Y', strtotime($payslipEmployee->salary_month . ' ' . $payslipEmployee->time));
                if (isset($setting['monthly_payslip_notification']) && $setting['monthly_payslip_notification'] == 1) {
                    $msg = ("payslip generated of") . ' ' . $monthYear . '.';
                    Utility::send_slack_msg($msg);
                }

                // telegram 
                $setting = Utility::settings();
                $monthYear = date('M Y', strtotime($payslipEmployee->salary_month . ' ' . $payslipEmployee->time));
                if (isset($setting['telegram_monthly_payslip_notification']) && $setting['telegram_monthly_payslip_notification'] == 1) {
                    $msg = ("payslip generated of") . ' ' . $monthYear . '.';
                    Utility::send_telegram_msg($msg);
                }


                // twilio
                $setting = Utility::settings();
                $emp = Employee::where('is_active', 1)->where('id', $payslipEmployee->employee_id = \Auth::user()->id)->first();
                if (isset($setting['twilio_payslip_notification']) && $setting['twilio_payslip_notification'] == 1) {
                    $employeess = Employee::where('is_active', 1)->where($request->employee_id)->orderby('name', 'asc')->get();
                    foreach ($employeess as $key => $employee) {
                        $msg = ("payslip generated of") . ' ' . $monthYear . '.';
                        Utility::send_twilio_msg($emp?->phone, $msg);
                    }
                }
            }

            // dd('bruto: ' . $employee->get_bruto_salary($month, $year) . ' - net salary: ' . $employee->get_net_salary($month, $year));

            return redirect()->back()->with('success', __('Payslip Successfully Created'));
        } else {
            return redirect()->back()->with('error', __('Payslip Already created.'));
        }
    }

    public function destroy($id)
    {
        $settings = Utility::settings();
        $payslip = PaySlip::find($id);

        if (!$payslip) {
            return redirect()->back()->with('error', __('Payslip not found.'));
        }

        $this->deletePayslipWithRelatedRecords($payslip, $settings);

        return redirect()->back()->with('success', __('Payslip Successfully Deleted'));
    }

    public function destroyPeriod(Request $request)
    {
        if (!\Auth::user()->can('Manage Pay Slip') || \Auth::user()->type == 'employee') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'month' => 'required|date_format:Y-m',
            'branch' => 'nullable|integer|exists:branches,id',
        ]);

        $employeeIds = $this->scopedEmployeeIds($request->branch);
        if ($employeeIds === false) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $query = PaySlip::where('salary_month', $request->month);
        if ($employeeIds !== null) {
            $query->whereIn('employee_id', $employeeIds);
        }

        $payslips = $query->get();
        if ($payslips->isEmpty()) {
            return redirect()->back()->with('error', __('No payslip data found for selected period.'));
        }

        $settings = Utility::settings();
        DB::transaction(function () use ($payslips, $settings) {
            foreach ($payslips as $payslip) {
                $this->deletePayslipWithRelatedRecords($payslip, $settings);
            }
        });

        return redirect()->back()->with('success', __('Payslip data for selected period successfully deleted.'));
    }

    private function deletePayslipWithRelatedRecords(PaySlip $payslip, array $settings): void
    {
        $month = date('m', strtotime($payslip->salary_month));
        $year = date('Y', strtotime($payslip->salary_month));

        $payslip->delete();

        $pph21 = Pph21::where('employee_id', $payslip->employee_id)->whereMonth('date', $month)->whereYear('date', $year)->first();
        if ($pph21) {
            if ($settings['pph21_autocut'] == 'on') {
                $pph21_deduction_option = DeductionOption::where('name', 'like', "%PPh21%")->first();
                $pph21_deduction = SaturationDeduction::where('employee_id', $pph21->employee_id)->where('period', "$year-$month")->where('deduction_option', $pph21_deduction_option?->id ?? 0)->where('amount', $pph21->pph21)->first();
                if ($pph21_deduction) {
                    $pph21_deduction->delete();
                }
            }

            $pph21->delete();
        }
    }

    private function accessibleBranchIds()
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

        return $branch_id;
    }

    private function scopedEmployeeIds($selectedBranch = null)
    {
        $branchIds = $this->accessibleBranchIds();

        if ($selectedBranch) {
            if ($branchIds->isNotEmpty() && !$branchIds->contains((int) $selectedBranch)) {
                return false;
            }

            return Employee::where('branch_id', $selectedBranch)->select('id')->get()->pluck('id');
        }

        if ($branchIds->isNotEmpty()) {
            return Employee::whereIn('branch_id', $branchIds)->select('id')->get()->pluck('id');
        }

        return null;
    }

    public function showemployee($paySlip)
    {

        $payslip = PaySlip::find($paySlip);

        return view('payslip.show', compact('payslip'));
    }
    public function search_json(Request $request)
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

        $formate_month_year = $request->datePicker;
        $validatePaysilp = $branch_id?->isNotEmpty() ? PaySlip::whereHas('employees', function ($query) use ($branch_id) {
            $query->whereIn('branch_id', $branch_id);
        })->where('salary_month', '=', $formate_month_year)->get()->toarray() : PaySlip::where('salary_month', '=', $formate_month_year)->get()->toarray();

        $data = [];
        if (empty($validatePaysilp)) {
            $data = [];
            return;
        } else {
            $paylip_employee = PaySlip::select(
                [
                    'employees.id',
                    'employees.employee_id',
                    'employees.name',
                    'payslip_types.name as payroll_type',
                    'pay_slips.basic_salary',
                    'pay_slips.net_payble',
                    'pay_slips.id as pay_slip_id',
                    'pay_slips.status',
                    'employees.user_id',
                ]
            )->leftjoin(
                    'employees',
                    function ($join) use ($formate_month_year) {
                        $join->on('employees.id', '=', 'pay_slips.employee_id');
                        $join->on('pay_slips.salary_month', '=', \DB::raw("'" . $formate_month_year . "'"));
                        $join->leftjoin('payslip_types', 'payslip_types.id', '=', 'employees.salary_type');
                    }
                )->where('employees.created_by', \Auth::user()->creatorId())->get();


            foreach ($paylip_employee as $employee) {

                if (Auth::user()->type == 'employee' && Auth::user()->id == $employee->user_id) {
                    $tmp = [];
                    $tmp[] = $employee->id;
                    $tmp[] = $employee->name;
                    $tmp[] = $employee->payroll_type;
                    $tmp[] = $employee->pay_slip_id;
                    $tmp[] = !empty($employee->basic_salary) ? \Auth::user()->priceFormat($employee->basic_salary) : '-';
                    $tmp[] = !empty($employee->net_payble) ? \Auth::user()->priceFormat($employee->net_payble) : '-';
                    if ($employee->status == 1) {
                        $tmp[] = 'paid';
                    } else {
                        $tmp[] = 'unpaid';
                    }
                    $tmp[] = !empty($employee->pay_slip_id) ? $employee->pay_slip_id : 0;
                    $tmp['url'] = route('employee.show', Crypt::encrypt($employee->id));
                    $data[] = $tmp;
                } elseif (Auth::user()->type != 'employee') {
                    $tmp = [];
                    $tmp[] = $employee->id;
                    $tmp[] = $employee->employee_id;
                    $tmp[] = $employee->name;
                    $tmp[] = $employee->payroll_type;
                    $tmp[] = !empty($employee->basic_salary) ? \Auth::user()->priceFormat($employee->basic_salary) : '-';
                    $tmp[] = !empty($employee->net_payble) ? \Auth::user()->priceFormat($employee->net_payble) : '-';
                    if ($employee->status == 1) {
                        $tmp[] = 'Paid';
                    } else {
                        $tmp[] = 'UnPaid';
                    }
                    $tmp[] = !empty($employee->pay_slip_id) ? $employee->pay_slip_id : 0;
                    $tmp['url'] = route('employee.show', Crypt::encrypt($employee->id));
                    $data[] = $tmp;
                }
            }

            return $data;
        }
    }


    // public function search_json(Request $request)
    // {

    //     $formate_month_year = $request->datePicker;
    //     $validatePaysilp    = PaySlip::where('salary_month', '=', $formate_month_year)->get()->toarray();

    //     $data=[];
    //     if (empty($validatePaysilp)) 
    //     {
    //         $data=[];
    //         return $data;
    //     } else {
    //         $paylip_employee = PaySlip::select(
    //             [
    //                 'employees.id',
    //                 'employees.employee_id',
    //                 'employees.name',
    //                 'payslip_types.name as payroll_type',
    //                 'pay_slips.basic_salary',
    //                 'pay_slips.net_payble',
    //                 'pay_slips.id as pay_slip_id',
    //                 'pay_slips.status',
    //                 'employees.user_id',
    //             ]
    //         )->leftjoin(
    //             'employees',
    //             function ($join) use ($formate_month_year) {
    //                 $join->on('employees.id', '=', 'pay_slips.employee_id');
    //                 $join->on('pay_slips.salary_month', '=', \DB::raw("'" . $formate_month_year . "'"));
    //                 $join->leftjoin('payslip_types', 'payslip_types.id', '=', 'employees.salary_type');
    //             }
    //         )->where('employees.created_by', \Auth::user()->creatorId())->get();


    //         foreach ($paylip_employee as $employee) {

    //             if (Auth::user()->type == 'employee') {
    //                 if (Auth::user()->id == $employee->user_id) {
    //                     $tmp   = [];
    //                     $tmp[] = $employee->id;
    //                     $tmp[] = $employee->name;
    //                     $tmp[] = $employee->payroll_type;
    //                     $tmp[] = $employee->pay_slip_id;
    //                     $tmp[] = !empty($employee->basic_salary) ? \Auth::user()->priceFormat($employee->basic_salary) : '-';
    //                     $tmp[] = !empty($employee->net_payble) ? \Auth::user()->priceFormat($employee->net_payble) : '-';
    //                     if ($employee->status == 1) {
    //                         $tmp[] = 'paid';
    //                     } else {
    //                         $tmp[] = 'unpaid';
    //                     }
    //                     $tmp[]  = !empty($employee->pay_slip_id) ? $employee->pay_slip_id : 0;
    //                     $data[] = $tmp;
    //                 }
    //             } else {

    //                 $tmp   = [];
    //                 $tmp[] = $employee->id;
    //                 $tmp[] = $employee->employee_id;
    //                 $tmp[] = $employee->name;
    //                 $tmp[] = $employee->payroll_type;
    //                 $tmp[] = !empty($employee->basic_salary) ? \Auth::user()->priceFormat($employee->basic_salary) : '-';
    //                 $tmp[] = !empty($employee->net_payble) ? \Auth::user()->priceFormat($employee->net_payble) : '-';
    //                 if ($employee->status == 1) {
    //                     $tmp[] = 'Paid';
    //                 } else {
    //                     $tmp[] = 'UnPaid';
    //                 }
    //                 $tmp[]  = !empty($employee->pay_slip_id) ? $employee->pay_slip_id : 0;
    //                 $data[] = $tmp;
    //             }
    //         }

    //         return $data;
    //     }
    // }

    public function paysalary($id, $date)
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

        $employeePayslip = $branch_id?->isNotEmpty() ? PaySlip::whereHas('employees', function ($query) use ($branch_id) {
            $query->whereIn('branch_id', $branch_id);
        })->where('employee_id', '=', $id)->where('salary_month', $date)->first() : PaySlip::where('employee_id', '=', $id)->where('salary_month', $date)->first();
        if (!empty($employeePayslip)) {
            $employeePayslip->status = 1;
            $employeePayslip->save();

            return redirect()->back()->with('success', __('Pay Slip Successfully Paid'));
        } else {
            return redirect()->back()->with('error', __('Payslip Payment failed.'));
        }
    }

    public function bulk_pay_create($date)
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

        $Employees = $branch_id?->isNotEmpty() ? PaySlip::whereHas('employees', function ($query) use ($branch_id) {
            $query->whereIn('branch_id', $branch_id);
        })->where('salary_month', $date)->orderby('name', 'asc')->get() : PaySlip::where('salary_month', $date)->orderby('name', 'asc')->get();
        $unpaidEmployees = $branch_id?->isNotEmpty() ? PaySlip::whereHas('employees', function ($query) use ($branch_id) {
            $query->whereIn('branch_id', $branch_id);
        })->where('salary_month', $date)->where('status', '=', 0)->get() : PaySlip::where('salary_month', $date)->where('status', '=', 0)->get();

        return view('payslip.bulkcreate', compact('Employees', 'unpaidEmployees', 'date'));
    }

    public function bulkpayment(Request $request, $date)
    {
        if ($request->branch) {
            PaySlip::whereHas('employees', function ($query) use ($request) {
                $query->where('branch_id', $request->branch);
            })->where('salary_month', $date)->where('status', 0)->update(['status' => 1]);
        } else {
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

            $branch_id?->isNotEmpty() ? PaySlip::whereHas('employees', function ($query) use ($branch_id) {
                $query->whereIn('branch_id', $branch_id);
            })->where('salary_month', $date)->where('status', 0)->update(['status' => 1]) : PaySlip::where('salary_month', $date)->where('status', 0)->update(['status' => 1]);
        }

        return redirect()->back()->with('success', __('Payslip Bulk Payment successfully.'));
    }

    public function employeepayslip()
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

        $employees = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->where('user_id', \Auth::user()->id)->first() : Employee::where('user_id', \Auth::user()->id)->first();

        $payslip = $branch_id?->isNotEmpty() ? PaySlip::whereHas('employees', function ($query) use ($branch_id) {
            $query->whereIn('branch_id', $branch_id);
        })->where('employee_id', '=', $employees->id)->orderby('name', 'asc')->get() : PaySlip::where('employee_id', '=', $employees->id)->orderby('name', 'asc')->get();

        return view('payslip.employeepayslip', compact('payslip'));
    }

    public function pdf($id, $month)
    {
        $payslip = PaySlip::where('employee_id', $id)->where('salary_month', $month)->first();
        $employee = Employee::find($payslip->employee_id);

        $salaryType = $employee->salaryType?->name ?? '-';

        $payslipDetail = Utility::employeePayslipDetail($id, $month);

        $company_name = DB::table('settings')->select('value')->where('name', 'company_name')->first();

        return view('payslip.pdf', compact('payslip', 'employee', 'payslipDetail', 'company_name', 'salaryType'));
    }

    public function send($id, $month)
    {
        $payslip = PaySlip::where('employee_id', $id)->where('salary_month', $month)->first();
        $employee = Employee::find($payslip->employee_id);

        $payslip->name = $employee->name;
        $payslip->email = $employee->email;

        $payslipId = Crypt::encrypt($payslip->id);
        $payslip->url = route('payslip.payslipPdf', [$payslipId, $month]);

        $setings = Utility::settings();
        if ($setings['new_payroll'] == 1) {
            $uArr = [
                'payslip_email' => $payslip->email,
                'name' => $payslip->name,
                'url' => $payslip->url,
                'salary_month' => $payslip->salary_month,
            ];

            $resp = Utility::sendEmailTemplate('new_payroll', [$payslip->email], $uArr);
            return redirect()->back()->with('success', __('Payslip successfully sent.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        }

        return redirect()->back()->with('success', __('Payslip successfully sent.'));
    }

    public function payslipPdf($id, $month)
    {
        $payslipId = Crypt::decrypt($id);

        $payslip = PaySlip::where('id', $payslipId)->first();
        $employee = Employee::find($payslip->employee_id);

        $salaryType = $employee->salaryType?->name ?? '-';

        $payslipDetail = Utility::employeePayslipDetail($payslip->employee_id, $month);

        $company_name = DB::table('settings')->select('value')->where('name', 'company_name')->first();

        return view('payslip.payslipPdf', compact('payslip', 'employee', 'payslipDetail', 'company_name', 'salaryType'));
    }

    public function editEmployee($paySlip, Request $request)
    {
        $payslip = PaySlip::find($paySlip);

        return view('payslip.salaryEdit', compact('payslip'));
    }

    public function updateEmployee(Request $request, $id)
    {
        $month = date('m', strtotime($request->month)) ?? date('m');
        $year = date('Y', strtotime($request->month)) ?? date('Y');
        if (isset($request->allowance) && !empty($request->allowance)) {
            $allowances = $request->allowance;
            $allowanceIds = $request->allowance_id;
            foreach ($allowances as $k => $allownace) {
                $allowanceData = Allowance::find($allowanceIds[$k]);
                $allowanceData->amount = $allownace;
                $allowanceData->save();
            }
        }


        if (isset($request->commission) && !empty($request->commission)) {
            $commissions = $request->commission;
            $commissionIds = $request->commission_id;
            foreach ($commissions as $k => $commission) {
                $commissionData = Commission::find($commissionIds[$k]);
                $commissionData->amount = $commission;
                $commissionData->save();
            }
        }

        if (isset($request->loan) && !empty($request->loan)) {
            $loans = $request->loan;
            $loanIds = $request->loan_id;
            foreach ($loans as $k => $loan) {
                $loanData = Loan::find($loanIds[$k]);
                $loanData->amount = $loan;
                $loanData->save();
            }
        }


        if (isset($request->saturation_deductions) && !empty($request->saturation_deductions)) {
            $saturation_deductionss = $request->saturation_deductions;
            $saturation_deductionsIds = $request->saturation_deductions_id;
            foreach ($saturation_deductionss as $k => $saturation_deductions) {

                $saturation_deductionsData = SaturationDeduction::find($saturation_deductionsIds[$k]);
                $saturation_deductionsData->amount = $saturation_deductions;
                $saturation_deductionsData->save();
            }
        }


        if (isset($request->other_payment) && !empty($request->other_payment)) {
            $other_payments = $request->other_payment;
            $other_paymentIds = $request->other_payment_id;
            foreach ($other_payments as $k => $other_payment) {
                $other_paymentData = OtherPayment::find($other_paymentIds[$k]);
                $other_paymentData->amount = $other_payment;
                $other_paymentData->save();
            }
        }


        if (isset($request->rate) && !empty($request->rate)) {
            $rates = $request->rate;
            $rateIds = $request->rate_id;
            $hourses = $request->hours;

            foreach ($rates as $k => $rate) {
                $overtime = Overtime::find($rateIds[$k]);
                $overtime->rate = $rate;
                $overtime->hours = $hourses[$k];
                $overtime->save();
            }
        }


        $payslipEmployee = PaySlip::find($request->payslip_id);
        $payslipEmployee->allowance = Employee::allowance($payslipEmployee->employee_id, $month, $year);
        $payslipEmployee->commission = Employee::commission($payslipEmployee->employee_id, $month, $year);
        $payslipEmployee->loan = Employee::loan($payslipEmployee->employee_id, $month, $year);
        $payslipEmployee->saturation_deduction = Employee::saturation_deduction($payslipEmployee->employee_id, $month, $year);
        $payslipEmployee->other_payment = Employee::other_payment($payslipEmployee->employee_id, $month, $year);
        $payslipEmployee->overtime = Employee::get_overtime($payslipEmployee->employee_id, $month, $year);
        $payslipEmployee->net_payble = Employee::find($payslipEmployee->employee_id)->get_net_salary($month, $year);
        $payslipEmployee->bruto = Employee::find($payslipEmployee->employee_id)->get_bruto_salary($month, $year);
        $payslipEmployee->save();

        return redirect()->back()->with('success', __('Employee payroll successfully updated.'));
    }

    public function PayslipExport(Request $request)
    {
        $name = 'payslip_' . date('Y-m-d i:h:s');
        $data = Excel::download(new PayslipExport($request), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }

    public function payslipAuth(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'password' => 'required',
                'payslip_id' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        if (Hash::check($request->password, Auth::user()->password) && !empty($request->payslip_id)) {
            try {
                $payslip = PaySlip::find($request->payslip_id);
                $employee = Employee::find($payslip->employee_id);

                $payslipDetail = Utility::employeePayslipDetail($employee->id, $payslip->salary_month);

                $salaryType = $employee->salaryType?->name ?? '-';

                $company_name = DB::table('settings')->select('value')->where('name', 'company_name')->first();

                return view('payslip.pdf', compact('payslip', 'employee', 'payslipDetail', 'company_name', 'salaryType'));
            } catch (\Throwable $th) {
                return response()->json(['error' => __('Something Wrong Happened, Please Refresh The Page')]);
            }
        } else {
            return response()->json(['error' => __('Wrong Password')]);
        }
    }
}
