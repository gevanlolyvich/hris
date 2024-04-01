<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Report;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmployeeReportController extends Controller
{
    public function index(Request $request)
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

        $branch = $branch_id?->isNotEmpty() ? Branch::whereIn('id', $branch_id)->get()->pluck('name', 'id') : Branch::get()->pluck('name', 'id');

        $department = $branch_id?->isNotEmpty() ? Department::whereIn('branch_id', $branch_id)->get()->pluck('name', 'id') : Department::get()->pluck('name', 'id');

        if(Auth::user()->type == 'employee')
        {
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

                $reports = Report::whereIn('employee_id', $employees);
            } else {
                $reports = Report::where('employee_id', $emp);
            }
        }
        else
        {
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

            $employee = $employee?->orderby('name', 'asc')?->get()?->pluck('id');

            $reports = Report::whereIn('employee_id', $employee);
        }

        if ($request->type) {
            $reports = $reports->where('type', $request->type);
        }

        $type   = Report::$report_type;
        foreach ($type as $index => $name) {
            $type[$index] = __($name);
        }

        return view('employee_report.index', compact('reports', 'branch', 'department', 'type'));
    }

    public function create()
    {
    }

    public function store(Request $request)
    {
    }

    public function show(Report $report)
    {
    }

    public function edit(Report $report)
    {
    }

    public function update(Request $request, Report $report)
    {
    }

    public function destroy(Report $report)
    {
    }
}
