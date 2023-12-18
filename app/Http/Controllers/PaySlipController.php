<?php

namespace App\Http\Controllers;

use App\Exports\PayslipExport;
use App\Models\Allowance;
use App\Models\Commission;
use App\Models\Employee;
use App\Models\Loan;
use App\Mail\InvoiceSend;
use App\Mail\PayslipSend;
use App\Models\OtherPayment;
use App\Models\Overtime;
use App\Models\PaySlip;
use App\Models\SaturationDeduction;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class PaySlipController extends Controller
{

    public function index(Request $request)
    {
        $month  = $request->month ?? date('Y-m', strtotime(date('Y-m') . ' -1 month'));
        if (\Auth::user()->can('Manage Pay Slip') && \Auth::user()->type == 'employee') {
            $subordinates = \Auth::user()->employee->subordinatesFlatten();

            // Check if employee managing other employee or not
            $employees = collect();
            if ($subordinates->isNotEmpty()) {
                foreach ($subordinates as $subordinate) {
                    $employees->push($subordinate->id);
                }

                $employees->push(\Auth::user()->employee->id);
            } else {
                $employees->push(\Auth::user()->employee->id);
            }

            $payslips = PaySlip::where('salary_month', $month)->whereIn('employeeId', $employees)->get();

            return view('payslip.index', compact('payslips', 'month'));
        } elseif (\Auth::user()->type != 'employee') {
            $payslips = PaySlip::where('salary_month', $month)->get();

            return view('payslip.index', compact('payslips', 'month'));
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

        $validatePaysilp    = PaySlip::where('salary_month', '=', $formate_month_year)->where('created_by', \Auth::user()->creatorId())->pluck('employee_id');
        $payslip_employee   = Employee::where('created_by', \Auth::user()->creatorId())->where('company_doj', '<=', date($year . '-' . $month . '-t'))->count();

        if ($payslip_employee > count($validatePaysilp)) {
            $employees = Employee::where('created_by', \Auth::user()->creatorId())->where('company_doj', '<=', date($year . '-' . $month . '-t'))->whereNotIn('employee_id', $validatePaysilp)->get();

            // check if there is employe that salary has to be set
            $employeesSalary = Employee::where('created_by', \Auth::user()->creatorId())->where('salary', '<=', 0)->first();

            if (!empty($employeesSalary)) {
                return redirect()->back()->with('error', __('Please set employee salary.'));
            }

            foreach ($employees as $employee) {
                $payslipEmployee                       = new PaySlip();
                $payslipEmployee->employee_id          = $employee->id;
                $payslipEmployee->net_payble           = $employee->get_net_salary($month, $year);
                $payslipEmployee->salary_month         = $formate_month_year;
                $payslipEmployee->status               = 0; // not paid yet
                $payslipEmployee->basic_salary         = $employee->get_salary($month, $year);
                $payslipEmployee->allowance            = Employee::allowance($employee->id, $month, $year);
                $payslipEmployee->commission           = Employee::commission($employee->id, $month, $year);
                $payslipEmployee->loan                 = Employee::loan($employee->id, $month, $year);
                $payslipEmployee->saturation_deduction = Employee::saturation_deduction($employee->id);
                $payslipEmployee->other_payment        = Employee::other_payment($employee->id);
                $payslipEmployee->overtime             = Employee::get_overtime($employee->id, $month, $year);
                $payslipEmployee->created_by           = \Auth::user()->creatorId();

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
                $setting  = Utility::settings();
                $emp = Employee::where('id', $payslipEmployee->employee_id = \Auth::user()->id)->first();
                if (isset($setting['twilio_payslip_notification']) && $setting['twilio_payslip_notification'] == 1) {
                    $employeess = Employee::where($request->employee_id)->orderby('name', 'asc')->get();
                    foreach ($employeess as $key => $employee) {
                        $msg = ("payslip generated of") . ' ' . $monthYear . '.';
                        Utility::send_twilio_msg($emp->phone, $msg);
                    }
                }
            }

            return redirect()->back()->with('success', __('Payslip successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Payslip Already created.'));
        }
    }

    public function destroy($id)
    {
        $payslip = PaySlip::find($id);
        $payslip->delete();

        return redirect()->back()->with('success', __('Payslip successfully deleted.'));
    }

    public function showemployee($paySlip)
    {

        $payslip = PaySlip::find($paySlip);

        return view('payslip.show', compact('payslip'));
    }
    public function search_json(Request $request)
    {

        $formate_month_year = $request->datePicker;
        $validatePaysilp    = PaySlip::where('salary_month', '=', $formate_month_year)->where('created_by', \Auth::user()->creatorId())->get()->toarray();

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
                    $tmp   = [];
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
                    $tmp[]  = !empty($employee->pay_slip_id) ? $employee->pay_slip_id : 0;
                    $tmp['url']  = route('employee.show', Crypt::encrypt($employee->id));
                    $data[] = $tmp;
                } elseif (Auth::user()->type != 'employee') {
                    $tmp   = [];
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
                    $tmp[]  = !empty($employee->pay_slip_id) ? $employee->pay_slip_id : 0;
                    $tmp['url']  = route('employee.show', Crypt::encrypt($employee->id));
                    $data[] = $tmp;
                }
            }

            return $data;
        }
    }


    // public function search_json(Request $request)
    // {

    //     $formate_month_year = $request->datePicker;
    //     $validatePaysilp    = PaySlip::where('salary_month', '=', $formate_month_year)->where('created_by', \Auth::user()->creatorId())->get()->toarray();

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
        $employeePayslip = PaySlip::where('employee_id', '=', $id)->where('created_by', \Auth::user()->creatorId())->where('salary_month', $date)->first();
        if (!empty($employeePayslip)) {
            $employeePayslip->status = 1;
            $employeePayslip->save();

            return redirect()->back()->with('success', __('Payslip Payment successfully.'));
        } else {
            return redirect()->back()->with('error', __('Payslip Payment failed.'));
        }
    }

    public function bulk_pay_create($date)
    {
        $Employees       = PaySlip::where('salary_month', $date)->where('created_by', \Auth::user()->creatorId())->orderby('name', 'asc')->get();
        $unpaidEmployees = PaySlip::where('salary_month', $date)->where('created_by', \Auth::user()->creatorId())->where('status', '=', 0)->get();

        return view('payslip.bulkcreate', compact('Employees', 'unpaidEmployees', 'date'));
    }

    public function bulkpayment(Request $request, $date)
    {
        PaySlip::where('salary_month', $date)->where('created_by', \Auth::user()->creatorId())->where('status', 0)->update(['status' => 1]);

        return redirect()->back()->with('success', __('Payslip Bulk Payment successfully.'));
    }

    public function employeepayslip()
    {
        $employees = Employee::where(
            [
                'user_id' => \Auth::user()->id,
            ]
        )->first();

        $payslip = PaySlip::where('employee_id', '=', $employees->id)->orderby('name', 'asc')->get();

        return view('payslip.employeepayslip', compact('payslip'));
    }

    public function pdf($id, $month)
    {
        $payslip  = PaySlip::where('employee_id', $id)->where('salary_month', $month)->where('created_by', \Auth::user()->creatorId())->first();
        $employee = Employee::find($payslip->employee_id);

        $payslipDetail = Utility::employeePayslipDetail($id, $month);

        $company_name = DB::table('settings')->select('value')->where('name', 'company_name')->first();

        return view('payslip.pdf', compact('payslip', 'employee', 'payslipDetail', 'company_name'));
    }

    public function send($id, $month)
    {
        $payslip  = PaySlip::where('employee_id', $id)->where('salary_month', $month)->where('created_by', \Auth::user()->creatorId())->first();
        $employee = Employee::find($payslip->employee_id);

        $payslip->name  = $employee->name;
        $payslip->email = $employee->email;

        $payslipId    = Crypt::encrypt($payslip->id);
        $payslip->url = route('payslip.payslipPdf', [$payslipId, $month]);

        $setings = Utility::settings();
        if ($setings['new_payroll'] == 1) {
            $uArr = [
                'payslip_email' => $payslip->email,
                'name'  => $payslip->name,
                'url' => $payslip->url,
                'salary_month' => $payslip->salary_month,
            ];

            $resp = Utility::sendEmailTemplate('new_payroll', [$payslip->email], $uArr);
            return redirect()->back()->with('success', __('Payslip successfully sent.')  . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        }

        return redirect()->back()->with('success', __('Payslip successfully sent.'));
    }

    public function payslipPdf($id, $month)
    {
        $payslipId = Crypt::decrypt($id);

        $payslip  = PaySlip::where('id', $payslipId)->where('created_by', \Auth::user()->creatorId())->first();
        $employee = Employee::find($payslip->employee_id);

        $payslipDetail = Utility::employeePayslipDetail($payslip->employee_id, $month);

        $company_name = DB::table('settings')->select('value')->where('name', 'company_name')->first();

        return view('payslip.payslipPdf', compact('payslip', 'employee', 'payslipDetail', 'company_name'));
    }

    public function editEmployee($paySlip, Request $request)
    {
        $payslip = PaySlip::find($paySlip);

        return view('payslip.salaryEdit', compact('payslip'));
    }

    public function updateEmployee(Request $request, $id)
    {
        $month = date('m', strtotime($request->month)) ?? date('m');
        $year  = date('Y', strtotime($request->month)) ?? date('Y');
        if (isset($request->allowance) && !empty($request->allowance)) {
            $allowances   = $request->allowance;
            $allowanceIds = $request->allowance_id;
            foreach ($allowances as $k => $allownace) {
                $allowanceData         = Allowance::find($allowanceIds[$k]);
                $allowanceData->amount = $allownace;
                $allowanceData->save();
            }
        }


        if (isset($request->commission) && !empty($request->commission)) {
            $commissions   = $request->commission;
            $commissionIds = $request->commission_id;
            foreach ($commissions as $k => $commission) {
                $commissionData         = Commission::find($commissionIds[$k]);
                $commissionData->amount = $commission;
                $commissionData->save();
            }
        }

        if (isset($request->loan) && !empty($request->loan)) {
            $loans   = $request->loan;
            $loanIds = $request->loan_id;
            foreach ($loans as $k => $loan) {
                $loanData         = Loan::find($loanIds[$k]);
                $loanData->amount = $loan;
                $loanData->save();
            }
        }


        if (isset($request->saturation_deductions) && !empty($request->saturation_deductions)) {
            $saturation_deductionss   = $request->saturation_deductions;
            $saturation_deductionsIds = $request->saturation_deductions_id;
            foreach ($saturation_deductionss as $k => $saturation_deductions) {

                $saturation_deductionsData         = SaturationDeduction::find($saturation_deductionsIds[$k]);
                $saturation_deductionsData->amount = $saturation_deductions;
                $saturation_deductionsData->save();
            }
        }


        if (isset($request->other_payment) && !empty($request->other_payment)) {
            $other_payments   = $request->other_payment;
            $other_paymentIds = $request->other_payment_id;
            foreach ($other_payments as $k => $other_payment) {
                $other_paymentData         = OtherPayment::find($other_paymentIds[$k]);
                $other_paymentData->amount = $other_payment;
                $other_paymentData->save();
            }
        }


        if (isset($request->rate) && !empty($request->rate)) {
            $rates   = $request->rate;
            $rateIds = $request->rate_id;
            $hourses = $request->hours;

            foreach ($rates as $k => $rate) {
                $overtime        = Overtime::find($rateIds[$k]);
                $overtime->rate  = $rate;
                $overtime->hours = $hourses[$k];
                $overtime->save();
            }
        }


        $payslipEmployee                       = PaySlip::find($request->payslip_id);
        $payslipEmployee->allowance            = Employee::allowance($payslipEmployee->employee_id, $month, $year);
        $payslipEmployee->commission           = Employee::commission($payslipEmployee->employee_id, $month, $year);
        $payslipEmployee->loan                 = Employee::loan($payslipEmployee->employee_id, $month, $year);
        $payslipEmployee->saturation_deduction = Employee::saturation_deduction($payslipEmployee->employee_id);
        $payslipEmployee->other_payment        = Employee::other_payment($payslipEmployee->employee_id);
        $payslipEmployee->overtime             = Employee::get_overtime($payslipEmployee->employee_id, $month, $year);
        $payslipEmployee->net_payble           = Employee::find($payslipEmployee->employee_id)->get_net_salary();
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
}
