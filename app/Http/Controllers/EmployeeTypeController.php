<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\EmployeeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EmployeeTypeController extends Controller
{
    public function index()
    {
        if (Auth::user()->type == 'company' || (Auth::user()->type == 'hr' && !(Auth::user()->branch_id))) {

            $employee_types = EmployeeType::get();
            // $department_id  = !empty(Auth::user()->branch_id) ? Department::where('branch_id', Auth::user()->branch_id)->get()->pluck('id')->toArray() : Department::get()->pluck('id')->toArray();
            // $designations = Designation::whereIn('department_id', $department_id)->orderBy('id', 'ASC')->get();

            // return $employee_types;
            return view('employeetype.index', compact('employee_types'));
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

            return view('employeetype.create', compact('types'));
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

            $employee_type->save();

            return redirect()->route('employeetype.index')->with('success', __('Employee Type successfully created'));
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
            $employee_type = EmployeeType::find($id);
            return view('employeetype.edit', compact('employee_type', 'types'));
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
            $employee_type->save();

            return redirect()->route('employeetype.index')->with('success', __('Employee Type successfully updated'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->type == 'company' || (Auth::user()->type == 'hr' && !(Auth::user()->branch_id))) {
            $employee_type = EmployeeType::find($id);
            $employee_type->delete();

            return redirect()->route('employeetype.index')->with('success', __('Employee Type successfully deleted'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
