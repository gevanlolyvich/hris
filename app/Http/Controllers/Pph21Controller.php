<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\PaySlip;
use App\Models\Pph21;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Pph21Controller extends Controller
{
    public function index(Request $request)
    {
        $month  = $request->month ?? date('Y-m', strtotime(date('Y-m') . ' -1 month'));


        $branch_id = collect();
        
        if (\Auth::user()->type == 'employee') {
            $pph21      = Pph21::where('employee_id', \Auth::user()->employee->id);
            $branch_id->push(\Auth::user()->employee->branch_id);
        } else {
            $branch = Branch::find(\Auth::user()->branch_id);
            if ($branch) {
                $branch_id->push($branch?->id);
            }
    
            $children = $branch?->childBranchFlatten();
            if ($children?->isNotEmpty()) {
                foreach ($children as $child) {
                    $branch_id->push($child->id);
                }
            }

            $employee = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->select('id') : Employee::select('id');
            if (!empty($request->branch)) {
                $employee->where('branch_id', $request->branch);
            }

            if (!empty($request->department)) {
                $employee->where('department_id', $request->department);
            }

            if (empty($request->department) && empty($request->branch)) {
                $department = [];
            }

            $employee   = $employee?->orderby('name', 'asc')?->get()?->pluck('id');

            $pph21      = Pph21::whereIn('employee_id', $employee);
        }

        if (!empty($request->month)) {
            $month = date('m', strtotime($request->month));
            $year  = date('Y', strtotime($request->month));

            $start_date = date($year . '-' . $month . '-01');
            $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

            $pph21->whereBetween(
                'date',
                [
                    $start_date,
                    $end_date,
                ]
            );
        } else {
            $month = date('m', strtotime("first day of last month"));
            $year  = date('Y');

            $start_date = date($year . '-' . $month . '-01');
            $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

            $pph21->whereBetween(
                'date',
                [
                    $start_date,
                    $end_date,
                ]
            );
        }

        if ($request->branch) {
            $employees  = Employee::where('branch_id', $request->branch)->select('id')->get()->pluck('id');
            $pph21      = $pph21->orderBy('date', 'desc')->withAggregate('employee', 'name')->orderBy('employee_name', 'asc')->get();
            $branch     = $branch_id?->isNotEmpty() ? Branch::whereIn('id', $branch_id)->get()->pluck('name', 'id') : Branch::get()->pluck('name', 'id');
        } else {
            $pph21      = $pph21->orderBy('date', 'desc')->withAggregate('employee', 'name')->orderBy('employee_name', 'asc')->get();
            $branch     = $branch_id?->isNotEmpty() ? Branch::whereIn('id', $branch_id)->get()->pluck('name', 'id') : Branch::get()->pluck('name', 'id');
        }

        return view('pph21.index', compact('month', 'pph21', 'branch'));
    }

    public function create()
    {
        return redirect()->back();
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'month' => 'required',
                'branch' => 'nullable|numeric',
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

        // check if every employee already have pph 21 for requested month
        if ($request->branch) {
            $exist_pph21    = Pph21::whereHas('employee', function ($query) use ($request) { $query->where('branch_id', $request->branch); })->whereMonth('date', $month)->whereYear('date', $year)->pluck('employee_id');
            $total_employee = Employee::where('branch_id', $request->branch)->where('is_active', 1)->where('company_doj', '<=', date($year . '-' . $month . '-t'))->count();
        } elseif (!$request->branch && \Auth::user()->branch_id) {
            $exist_pph21    = Pph21::whereHas('employee', function ($query) use ($request) { $query->where('branch_id', \Auth::user()->branch_id); })->whereMonth('date', $month)->whereYear('date', $year)->pluck('employee_id');
            $total_employee = Employee::where('branch_id', \Auth::user()->branch_id)->where('is_active', 1)->where('company_doj', '<=', date($year . '-' . $month . '-t'))->count();
        } else {
            $exist_pph21     = $branch_id?->isNotEmpty() ? Pph21::whereHas('employee', function ($query) use ($branch_id) { $query->whereIn('branch_id', $branch_id); })->whereMonth('date', $month)->whereYear('date', $year)->pluck('employee_id') : Pph21::whereMonth('date', $month)->whereYear('date', $year)->pluck('employee_id');
            $total_employee  = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->where('is_active', 1)->where('company_doj', '<=', date($year . '-' . $month . '-t'))->count() : Employee::where('is_active', 1)->where('company_doj', '<=', date($year . '-' . $month . '-t'))->count();
        }

        if ($total_employee > count($exist_pph21)) {
            if ($request->branch) {
                $employees = Employee::where('branch_id', $request->branch)->where('is_active', 1)->where('company_doj', '<=', date($year . '-' . $month . '-t'))->whereNotIn('employee_id', $exist_pph21)->get()->pluck('id');                 
            } else {
                $employees = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->where('is_active', 1)->where('company_doj', '<=', date($year . '-' . $month . '-t'))->whereNotIn('employee_id', $exist_pph21)->get()->pluck('id') : Employee::where('is_active', 1)->where('company_doj', '<=', date($year . '-' . $month . '-t'))->whereNotIn('employee_id', $exist_pph21)->get()->pluck('id');
            }

            // check if there employee missing payslip in the time frame
            $employeeHavePayslip = collect($employees)->every(function ($employee_id) use ($formate_month_year) {
                return PaySlip::where('employee_id', $employee_id)->where('salary_month', $formate_month_year)->exists();
            });


            if (!$employeeHavePayslip) {
                return redirect()->back()->with('error', __('Please Generate Employee Payslip First'));
            }

            $payslips   = PaySlip::whereIn('employee_id', $employees)->where('salary_month', $formate_month_year)->get();

            $month_in_year             = [];
            for ($i=1; $i <= 12 ; $i++) { 
                $date                       = $i > 9 ? $i : "0{$i}";
                $month_in_year[]            = "{$year}-{$date}";
            }

            foreach ($payslips as $payslip) {
                $ptkp                   = $this->get_ptkp($payslip->employees);

                // crete new pph21
                $pph21                  = new Pph21();
                $pph21->employee_id     = $payslip->employee_id;
                $pph21->date            = date('Y-m-t', strtotime($formate_month_year));
                $pph21->ptkp            = $ptkp;
                $pph21->tax_object_code = "21-100-01";
                $pph21->is_gross_up     = false;
                if ($month != 12) {
                    // Calculating PPh 21 For Normal Month (Non December)
                    $rate                   = $this->get_pph_ter_rate($ptkp, $payslip->bruto);

                    
                    $pph21->bruto           = $payslip->bruto;
                    $pph21->rate            = $rate;
                    $pph21->pph21           = $rate * $payslip->bruto;
                    
                } else {
                    $pph21->bruto           = $payslip->bruto;
                    $pph21->rate            = 0;

                    $deduction_in_year      = PaySlip::where('employee_id', $payslip->employee_id)->whereIn('salary_month', $month_in_year)->select('saturation_deduction')->get()->pluck('saturation_deduction');
                    $total_bruto            = PaySlip::where('employee_id', $payslip->employee_id)->whereIn('salary_month', $month_in_year)->sum('bruto');
                    $total_pph21            = Pph21::where('employee_id', $payslip->employee_id)->whereYear('date', $year)->sum('pph21');
                    $total_zakat            = $this->calculate_total_zakat($deduction_in_year, "{$payslip->employees->salary}");
                    $position_cost          = bcmul('0.05', $total_bruto, 2);
                    if ((float) $position_cost > 6000000) {
                        $position_cost      = '6000000.00';
                    }

                    $total_deduction        = bcadd($total_zakat, $position_cost, 2);
                    $net_in_year            = bcsub($total_bruto, $total_deduction, 2);

                    $ptkp_deduction         = $this->get_ptkp_deduction($ptkp);

                    $net_final              = bcsub($net_in_year, $ptkp_deduction, 2);
                    if (bccomp('0', $net_final) == 1 || bccomp('0', $net_final) == 0) {
                        $pph21->pph21           = 0;
                    } else {
                        $pph_year_owed          = $this->get_general_rate($net_final);
                        $december_pph           = bcsub($pph_year_owed, $total_pph21, 2);
                       
                        $pph21->pph21           = $december_pph;
                    }
                }

                $pph21->save();
            }

            return redirect()->back()->with('success', __('PPh 21 successfully created.'));
        } else {
            return redirect()->back()->with('error', __('PPh 21 Already Created'));
        }
    }

    public function show(Pph21 $pph21)
    {
        return redirect()->back();
    }

    public function edit(Pph21 $pph21)
    {
        return redirect()->back();
    }

    public function update(Request $request, Pph21 $pph21)
    {
        return redirect()->back();
    }

    public function destroy(Pph21 $pph21)
    {
        if (\Auth::user()->type == 'company' // admin
            || (\Auth::user()->type == 'hr' && (\Auth::user()->branch_id == null || $pph21?->employee?->branch_id == \Auth::user()->branch_id)) // HR
        ) {
            $pph21->delete();
            return redirect()->back()->with('success', __('PPh 21 Successfully Deleted'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function export(Request $request)
    {
        return redirect()->back();
    }

    public function get_ptkp(Employee $employee): String {
        $status     = null;
        $dependents = null;
        if ($employee->gender == 'Female') {
            $status     = 'TK';
            $dependents = '0';
        } else {
            if ($employee->marital_status == 'Married') {
                $status = 'K';
            } else {
                $status     = 'TK';
            }
            $dependents =  $employee->dependents >= 3 ? 3 : $employee->dependents; 
        }
        return "$status/$dependents";
    }

    public function get_pph_ter_rate($ptkp, $bruto): Float {
        $rate = 0;
        if (in_array($ptkp, ['TK/0', 'TK/1', 'K/0'])) { // TER Category A
            switch (true) {
                case $bruto > 5400000 && $bruto <= 5650000:
                    $rate = 0.25;
                    break;
                case $bruto > 5650000 && $bruto <= 5950000:
                    $rate = 0.5;
                    break;
                case $bruto > 5950000 && $bruto <= 6300000:
                    $rate = 0.75;
                    break;
                case $bruto > 6300000 && $bruto <= 6750000:
                    $rate = 1;
                    break;
                case $bruto > 6750000 && $bruto <= 7500000:
                    $rate = 1.25;
                    break;
                case $bruto > 7500000 && $bruto <= 8550000:
                    $rate = 1.5;
                    break;
                case $bruto > 8550000 && $bruto <= 9650000:
                    $rate = 1.75;
                    break;
                case $bruto > 9650000 && $bruto <= 10050000:
                    $rate = 2;
                    break;
                case $bruto > 10050000 && $bruto <= 10350000:
                    $rate = 2.25;
                    break;
                case $bruto > 10350000 && $bruto <= 10700000:
                    $rate = 2.5;
                    break;
                case $bruto > 10700000 && $bruto <= 11050000:
                    $rate = 3;
                    break;
                case $bruto > 11050000 && $bruto <= 11600000:
                    $rate = 3.5;
                    break;
                case $bruto > 11600000 && $bruto <= 12500000:
                    $rate = 4;
                    break;
                case $bruto > 12500000 && $bruto <= 13750000:
                    $rate = 5;
                    break;
                case $bruto > 13750000 && $bruto <= 15100000:
                    $rate = 6;
                    break;
                case $bruto > 15100000 && $bruto <= 16950000:
                    $rate = 7;
                    break;
                case $bruto > 16950000 && $bruto <= 19750000:
                    $rate = 8;
                    break;
                case $bruto > 19750000 && $bruto <= 24150000:
                    $rate = 9;
                    break;
                case $bruto > 24150000 && $bruto <= 26450000:
                    $rate = 10;
                    break;
                case $bruto > 26450000 && $bruto <= 28000000:
                    $rate = 11;
                    break;
                case $bruto > 28000000 && $bruto <= 30050000:
                    $rate = 12;
                    break;
                case $bruto > 30050000 && $bruto <= 32400000:
                    $rate = 13;
                    break;
                case $bruto > 32400000 && $bruto <= 35400000:
                    $rate = 14;
                    break;
                case $bruto > 35400000 && $bruto <= 39100000:
                    $rate = 15;
                    break;
                case $bruto > 39100000 && $bruto <= 43850000:
                    $rate = 16;
                    break;
                case $bruto > 43850000 && $bruto <= 47800000:
                    $rate = 17;
                    break;
                case $bruto > 47800000 && $bruto <= 51400000:
                    $rate = 18;
                    break;
                case $bruto > 51400000 && $bruto <= 56300000:
                    $rate = 19;
                    break;
                case $bruto > 56300000 && $bruto <= 62200000:
                    $rate = 20;
                    break;
                case $bruto > 62200000 && $bruto <= 68600000:
                    $rate = 21;
                    break;
                case $bruto > 68600000 && $bruto <= 77500000:
                    $rate = 22;
                    break;
                case $bruto > 77500000 && $bruto <= 89000000:
                    $rate = 23;
                    break;
                case $bruto > 89000000 && $bruto <= 103000000:
                    $rate = 24;
                    break;
                case $bruto > 103000000 && $bruto <= 125000000:
                    $rate = 25;
                    break;
                case $bruto > 125000000 && $bruto <= 157000000:
                    $rate = 26;
                    break;
                case $bruto > 157000000 && $bruto <= 206000000:
                    $rate = 27;
                    break;
                case $bruto > 206000000 && $bruto <= 337000000:
                    $rate = 28;
                    break;
                case $bruto > 337000000 && $bruto <= 454000000:
                    $rate = 29;
                    break;
                case $bruto > 454000000 && $bruto <= 550000000:
                    $rate = 30;
                    break;
                case $bruto > 550000000 && $bruto <= 695000000:
                    $rate = 31;
                    break;
                case $bruto > 695000000 && $bruto <= 910000000:
                    $rate = 32;
                    break;
                case $bruto > 910000000 && $bruto <= 1400000000:
                    $rate = 33;
                    break;
                case $bruto > 1400000000:
                    $rate = 34;
                    break;
                default:
                    break;
            }
        } elseif (in_array($ptkp, ['TK/2', 'TK/3', 'K/1', 'K/2'])) { // TER Category B
            switch (true) {
                case $bruto > 6200000 && $bruto <= 6500000:
                    $rate = 0.25;
                    break;
                case $bruto > 6500000 && $bruto <= 6850000:
                    $rate = 0.5;
                    break;
                case $bruto > 6850000 && $bruto <= 7300000:
                    $rate = 0.75;
                    break;
                case $bruto > 7300000 && $bruto <= 9200000:
                    $rate = 1;
                    break;
                case $bruto > 9200000 && $bruto <= 10750000:
                    $rate = 1.5;
                    break;
                case $bruto > 10750000 && $bruto <= 11250000:
                    $rate = 2;
                    break;
                case $bruto > 11250000 && $bruto <= 11600000:
                    $rate = 2.5;
                    break;
                case $bruto > 11600000 && $bruto <= 12600000:
                    $rate = 3;
                    break;
                case $bruto > 12600000 && $bruto <= 13600000:
                    $rate = 4;
                    break;
                case $bruto > 13600000 && $bruto <= 14950000:
                    $rate = 5;
                    break;
                case $bruto > 14950000 && $bruto <= 16400000:
                    $rate = 6;
                    break;
                case $bruto > 16400000 && $bruto <= 18450000:
                    $rate = 7;
                    break;
                case $bruto > 18450000 && $bruto <= 21850000:
                    $rate = 8;
                    break;
                case $bruto > 21850000 && $bruto <= 26000000:
                    $rate = 9;
                    break;
                case $bruto > 26000000 && $bruto <= 27700000:
                    $rate = 10;
                    break;
                case $bruto > 27700000 && $bruto <= 29350000:
                    $rate = 11;
                    break;
                case $bruto > 29350000 && $bruto <= 31450000:
                    $rate = 12;
                    break;
                case $bruto > 31450000 && $bruto <= 33950000:
                    $rate = 13;
                    break;
                case $bruto > 33950000 && $bruto <= 37100000:
                    $rate = 14;
                    break;
                case $bruto > 37100000 && $bruto <= 41100000:
                    $rate = 15;
                    break;
                case $bruto > 41100000 && $bruto <= 45800000:
                    $rate = 16;
                    break;
                case $bruto > 45800000 && $bruto <= 49500000:
                    $rate = 17;
                    break;
                case $bruto > 49500000 && $bruto <= 53800000:
                    $rate = 18;
                    break;
                case $bruto > 53800000 && $bruto <= 58500000:
                    $rate = 19;
                    break;
                case $bruto > 58500000 && $bruto <= 64000000:
                    $rate = 20;
                    break;
                case $bruto > 64000000 && $bruto <= 71000000:
                    $rate = 21;
                    break;
                case $bruto > 71000000 && $bruto <= 80000000:
                    $rate = 22;
                    break;
                case $bruto > 80000000 && $bruto <= 93000000:
                    $rate = 23;
                    break;
                case $bruto > 93000000 && $bruto <= 109000000:
                    $rate = 24;
                    break;
                case $bruto > 109000000 && $bruto <= 129000000:
                    $rate = 25;
                    break;
                case $bruto > 129000000 && $bruto <= 163000000:
                    $rate = 26;
                    break;
                case $bruto > 163000000 && $bruto <= 211000000:
                    $rate = 27;
                    break;
                case $bruto > 211000000 && $bruto <= 374000000:
                    $rate = 28;
                    break;
                case $bruto > 374000000 && $bruto <= 459000000:
                    $rate = 29;
                    break;
                case $bruto > 459000000 && $bruto <= 555000000:
                    $rate = 30;
                    break;
                case $bruto > 555000000 && $bruto <= 704000000:
                    $rate = 31;
                    break;
                case $bruto > 704000000 && $bruto <= 957000000:
                    $rate = 32;
                    break;
                case $bruto > 957000000 && $bruto <= 1405000000:
                    $rate = 33;
                    break;
                case $bruto > 1405000000:
                    $rate = 34;
                    break;
                default:
                    break;
            }
        } elseif ($ptkp == 'TK/3') { // TER Category C
            switch (true) {
                case $bruto > 6600000 && $bruto <= 6950000:
                    $rate = 0.25;
                    break;
                case $bruto > 6950000 && $bruto <= 7350000:
                    $rate = 0.5;
                    break;
                case $bruto > 7350000 && $bruto <= 7800000:
                    $rate = 0.75;
                    break;
                case $bruto > 7800000 && $bruto <= 8850000:
                    $rate = 1;
                    break;
                case $bruto > 8850000 && $bruto <= 9800000:
                    $rate = 1.25;
                    break;
                case $bruto > 9800000 && $bruto <= 10950000:
                    $rate = 1.5;
                    break;
                case $bruto > 10950000 && $bruto <= 11200000:
                    $rate = 1.75;
                    break;
                case $bruto > 11200000 && $bruto <= 12050000:
                    $rate = 2;
                    break;
                case $bruto > 12050000 && $bruto <= 12950000:
                    $rate = 3;
                    break;
                case $bruto > 12950000 && $bruto <= 14150000:
                    $rate = 4;
                    break;
                case $bruto > 14150000 && $bruto <= 15550000:
                    $rate = 5;
                    break;
                case $bruto > 15550000 && $bruto <= 17050000:
                    $rate = 6;
                    break;
                case $bruto > 17050000 && $bruto <= 19500000:
                    $rate = 7;
                    break;
                case $bruto > 19500000 && $bruto <= 22700000:
                    $rate = 8;
                    break;
                case $bruto > 22700000 && $bruto <= 26600000:
                    $rate = 9;
                    break;
                case $bruto > 26600000 && $bruto <= 28100000:
                    $rate = 10;
                    break;
                case $bruto > 28100000 && $bruto <= 30100000:
                    $rate = 11;
                    break;
                case $bruto > 30100000 && $bruto <= 32600000:
                    $rate = 12;
                    break;
                case $bruto > 32600000 && $bruto <= 35400000:
                    $rate = 13;
                    break;
                case $bruto > 35400000 && $bruto <= 38900000:
                    $rate = 14;
                    break;
                case $bruto > 38900000 && $bruto <= 43000000:
                    $rate = 15;
                    break;
                case $bruto > 43000000 && $bruto <= 47400000:
                    $rate = 16;
                    break;
                case $bruto > 47400000 && $bruto <= 51200000:
                    $rate = 17;
                    break;
                case $bruto > 51200000 && $bruto <= 55800000:
                    $rate = 18;
                    break;
                case $bruto > 55800000 && $bruto <= 60400000:
                    $rate = 19;
                    break;
                case $bruto > 60400000 && $bruto <= 66700000:
                    $rate = 20;
                    break;
                case $bruto > 66700000 && $bruto <= 74500000:
                    $rate = 21;
                    break;
                case $bruto > 74500000 && $bruto <= 83200000:
                    $rate = 22;
                    break;
                case $bruto > 83200000 && $bruto <= 95600000:
                    $rate = 23;
                    break;
                case $bruto > 95600000 && $bruto <= 110000000:
                    $rate = 24;
                    break;
                case $bruto > 110000000 && $bruto <= 134000000:
                    $rate = 25;
                    break;
                case $bruto > 134000000 && $bruto <= 169000000:
                    $rate = 26;
                    break;
                case $bruto > 169000000 && $bruto <= 221000000:
                    $rate = 27;
                    break;
                case $bruto > 221000000 && $bruto <= 390000000:
                    $rate = 28;
                    break;
                case $bruto > 390000000 && $bruto <= 463000000:
                    $rate = 29;
                    break;
                case $bruto > 463000000 && $bruto <= 561000000:
                    $rate = 30;
                    break;
                case $bruto > 561000000 && $bruto <= 709000000:
                    $rate = 31;
                    break;
                case $bruto > 709000000 && $bruto <= 965000000:
                    $rate = 32;
                    break;
                case $bruto > 965000000 && $bruto <= 1419000000:
                    $rate = 33;
                    break;
                case $bruto > 1419000000:
                    $rate = 34;
                    break;
                default:
                    break;
            }
        }
        return $rate / 100;
    }

    public function calculate_total_zakat($payslips_deductions, $salary): String {
        $total_zakat = '0';
        foreach ($payslips_deductions as $payslip_deduction) {
            $deductions = json_decode($payslip_deduction);
            foreach ($deductions as $deduction) {
                $deduction = collect($deduction);

                if ($deduction['deduction_option'] == 2 && $deduction['type'] == 'percentage') {
                    $percentage     = bcdiv((string) $deduction['amount'], '100', 4);
                    $temp_zakat     = bcmul((string) $salary, $percentage, 2);
                    $total_zakat    = bcadd($total_zakat, $temp_zakat, 2);
                } elseif ($deduction['deduction_option'] == 2 && $deduction['type'] == 'fixed') {
                    $total_zakat    = bcadd($total_zakat, (string) $deduction['amount'], 2);
                }
            }
        }
        return $total_zakat;
    }

    public function get_ptkp_deduction ($ptkp): String {
        $deduction      = '0';
        $ptkp_category  = explode('/', $ptkp);
        if ($ptkp_category[0] == 'TK') {
            $deduction = '54000000';
        } elseif ($ptkp_category[0] == 'K') {
            $deduction = '58500000';
        }

        $dependents_addition = bcmul('4500000', $ptkp_category[1], 2);

        return bcadd($deduction, $dependents_addition, 2);
    }

    public function get_general_rate ($net): String {
        $total      = '0.00';
        $pph_17 = [
            [
                'rate' => '0.05',
                'start' => '0',
                'end' => '60000000',
            ],
            [
                'rate' => '0.15',
                'start' => '250000000',
                'end' => '60000000',
            ],
            [
                'rate' => '0.25',
                'start' => '500000000',
                'end' => '250000000',
            ],
            [
                'rate' => '0.30',
                'start' => '500000000',
                'end' => '5000000000',
            ],
            [
                'rate' => '0.35',
                'start' => '5000000000',
                'end' => '0',
            ],
        ];

        foreach ($pph_17 as $category) {
            if (bccomp($net, $category['end']) == 1 && $category['end'] != '0') {
                $temp_data  = bcmul($category['rate'], bcsub($category['end'], $category['start'], 2), 2);
                $total      = bcadd($temp_data, $temp_data, 2);
            } elseif (bccomp($net, $category['start']) == 1) {
                $total      = bcadd($total, bcmul($category['rate'], bcsub($net, $category['start'], 2), 2), 2);
            }
        }
        
        return $total;
    }
}
