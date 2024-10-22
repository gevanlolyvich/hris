<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeePeriod;
use App\Models\EmployeeType;
use App\Models\User;
use Google\Http\REST;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EmployeeApplicationController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->type == 'company' || (Auth::user()->type == 'hr')) {

            $query = EmployeePeriod::with('employee');

            if ($request->status) {
                $query->where('status', $request->status);
            }

            if (Auth::user()->branch_id) {
                $query->whereHas('employee', function ($query) {
                    $query->where('branch_id', Auth::user()->branch_id);
                });
            }

            $employee_periods = $query->orderBy('created_at', 'DESC')
                ->orderBy('status', 'DESC')
                ->get();

            return view('employee_period.index', compact('employee_periods'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (Auth::user()->type == 'company' || (Auth::user()->type == 'hr')) {
            // $departments  = !empty(\Auth::user()->branch_id) ? Department::orderBy('name', 'ASC')->where('branch_id', \Auth::user()->branch_id)->get()->pluck('name', 'id') : Department::orderBy('name', 'ASC')->get()->pluck('name', 'id');
            $types = [
                'Fixed' => __('Fixed'),
                'Flexible' => __('Flexible'),
            ];

            $period_types = [
                'Fixed' => __('Fixed'),
                'Periodical' => __('Periodical'),
            ];

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
            $periodical_types = EmployeeType::where('period_type', "Periodical")->get();
            // $employees = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->orderby('name', 'asc') : Employee::orderby('name', 'asc');
            $employees = Employee::whereIn('type_id', $periodical_types->pluck('id'));
            $employees = $branch_id?->isNotEmpty() ? $employees->whereIn('branch_id', $branch_id)->orderby('name', 'asc')->pluck('name', 'id') : $employees->orderby('name', 'asc')->pluck('name', 'id');

            $periodical_types = $periodical_types->pluck('name', 'id');
            // return $employees;
            return view('employee_period.create', compact('types', 'period_types', 'employees', 'periodical_types'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->type == 'company' || (Auth::user()->type == 'hr')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'employee_id'   => 'required',
                    'reason'        => 'required',
                    'period_type'   => 'required',
                    'start_period'  => 'required',
                    'end_period'    => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $employee_period                = new EmployeePeriod();
            $employee_period->employee_id   = $request->employee_id;
            $employee_period->type_id       = $request->period_type;
            $employee_period->reason        = $request->reason;
            $employee_period->start_period  = $request->start_period;
            $employee_period->end_period    = $request->end_period;
            $employee_period->created_by    = Auth::user()->id;

            if (Auth::user()->branch_id != null) {
                $employee_period->active = false;
                $employee_period->status = "Pending";
            } else {
                // $employee_period->active = true;
                $employee_period->status = "Approved";
            }

            $employee_period->save();

            return redirect()->back()->with('success', __('Employee Application Successfully Created'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Designation $designation)
    {
        return redirect()->route('designation.index');
    }

    public function edit($id)
    {

        if (Auth::user()->type == 'company' || (Auth::user()->type == 'hr')) {
            $period_types = [
                'Fixed' => __('Fixed'),
                'Periodical' => __('Periodical'),
            ];

            $employee_period = EmployeePeriod::find($id);
            $employee_types = EmployeeType::where('period_type', 'Periodical')->orderBy('name', 'ASC')->get()->pluck('name', 'id');

            return view('employee_period.edit', compact('period_types', 'employee_period', 'employee_types'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request)
    {
        if (Auth::user()->type == 'company' || (Auth::user()->type == 'hr')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'reason'        => 'required',
                    'period_type'   => 'required',
                    'start_period'  => 'required',
                    'end_period'    => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            // return $request;

            $employee_period = EmployeePeriod::find($request->id);
            $employee_period->reason        = $request->reason;
            $employee_period->start_period  = $request->start_period;
            $employee_period->end_period    = $request->end_period;
            $employee_period->status        = "Pending";
            $employee_period->save();

            $employee = $employee_period->employee;
            $employee->type_id = $request->period_type;
            $employee->save();

            return redirect()->back()->with('success', __('Employee Application Successfully Updated'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->type == 'company' || (Auth::user()->type == 'hr')) {
            $employee_period = EmployeePeriod::find($id);
            $employee_period->delete();

            return redirect()->back()->with('success', __('Employee Application Successfully Deleted'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function json(Request $request)
    {
        $type = EmployeeType::find($request->id);


        return response()->json($type);
    }

    public function action($id)
    {
        $employee_period = EmployeePeriod::find($id);
        $employee = Employee::find($employee_period->employee_id);

        return view('employee_period.action', compact('employee_period', 'employee'));
    }

    public function changeAction(Request $request)
    {
        if (Auth::user()->type == 'company' || (Auth::user()->type == 'hr')) {
            DB::transaction(function () use ($request) {
                $employee_period = EmployeePeriod::findOrFail($request->employee_period_id);
                $employee_period->update([
                    'response' => $request->response,
                    'status' => $request->status,
                    // 'active' => true,
                ]);

                // if ($request->status === "Approved") {
                //     // Aktifkan employee
                //     $employee = Employee::findOrFail($employee_period->employee_id);
                //     $employee->update(['is_active' => true]);

                //     // Aktifkan user
                //     $user = User::findOrFail($employee->user_id);

                //     $user->update(['is_active' => true]);
                // }
            });
            return redirect()->back()->with('success', __('Employee Application Successfully Updated'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
