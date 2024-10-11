<?php

namespace App\Http\Controllers;

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
    public function index()
    {
        if (Auth::user()->type == 'company' || (Auth::user()->type == 'hr' && !(Auth::user()->branch_id))) {

            $employee_periods = EmployeePeriod::orderBy('created_at', 'DESC')->orderBy('status', 'DESC')->get();
            return view('employee_period.index', compact('employee_periods'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (Auth::user()->type == 'company' || (Auth::user()->type == 'hr' && !(Auth::user()->branch_id))) {
            // $departments  = !empty(\Auth::user()->branch_id) ? Department::orderBy('name', 'ASC')->where('branch_id', \Auth::user()->branch_id)->get()->pluck('name', 'id') : Department::orderBy('name', 'ASC')->get()->pluck('name', 'id');
            $types = [
                'Fixed' => __('Fixed'),
                'Flexible' => __('Flexible'),
            ];

            $period_types = [
                'Fixed' => __('Fixed'),
                'Periodical' => __('Periodical'),
            ];

            return view('employee_period.create', compact('types', 'period_types'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {

        if (Auth::user()->type == 'company' || (Auth::user()->type == 'hr' && !(Auth::user()->branch_id))) {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|max:100',
                    'type' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $employee_type                = new EmployeeType();
            $employee_type->type          = $request->type;
            $employee_type->name          = $request->name;
            $employee_type->period_type   = $request->period_type;

            $employee_type->save();

            return redirect()->route('employee_period.index')->with('success', __('Employee Type successfully created'));
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

        if (Auth::user()->type == 'company' || (Auth::user()->type == 'hr' && !(Auth::user()->branch_id))) {
            $types = [
                'Fixed' => __('Fixed'),
                'Flexible' => __('Flexible'),
            ];

            $period_types = [
                'Fixed' => __('Fixed'),
                'Periodical' => __('Periodical'),
            ];

            $employee_type = EmployeeType::find($id);
            return view('employee_period.edit', compact('employee_type', 'types', 'period_types'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request)
    {
        // return $request;
        if (Auth::user()->type == 'company' || (Auth::user()->type == 'hr' && !(Auth::user()->branch_id))) {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|max:100',
                    'type' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $employee_type = EmployeeType::find($request->id);
            $employee_type->name = $request->name;
            $employee_type->type = $request->type;
            $employee_type->period_type = $request->period_type;
            $employee_type->save();

            return redirect()->route('employee_period.index')->with('success', __('Employee Type successfully updated'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->type == 'company' || (Auth::user()->type == 'hr' && !(Auth::user()->branch_id))) {
            $employee_type = EmployeeType::find($id);
            $employee_type->delete();

            return redirect()->route('employee_period.index')->with('success', __('Employee Type successfully deleted'));
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
        if (Auth::user()->type == 'company' || (Auth::user()->type == 'hr' && !(Auth::user()->branch_id))) {
            DB::transaction(function () use ($request) {
                $employee_period = EmployeePeriod::findOrFail($request->employee_period_id);
                $employee_period->update([
                    'response' => $request->response,
                    'status' => $request->status,
                    'active' => true,
                ]);

                if ($request->status === "Approved") {
                    // Aktifkan employee
                    $employee = Employee::findOrFail($employee_period->employee_id);
                    $employee->update(['is_active' => true]);

                    // Aktifkan user
                    $user = User::findOrFail($employee->user_id);

                    $user->update(['is_active' => true]);
                }
            });
            return redirect()->back()->with('success', __('Employee Application Successfully Updated'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
