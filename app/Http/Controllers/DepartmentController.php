<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('Manage Department')) {
            $departments = !empty(\Auth::user()->branch_id) ? Department::where('branch_id', \Auth::user()->branch_id)->orderBy('id', 'ASC')->get() : Department::orderBy('id', 'ASC')->get();

            return view('department.index', compact('departments'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Department')) {
            $branch = !empty(\Auth::user()->branch_id) ? Branch::where('id', \Auth::user()->branch_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id') : Branch::orderBy('name', 'ASC')->get()->pluck('name', 'id');

            return view('department.create', compact('branch'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Department')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'branch_id' => 'required',
                    'name' => 'required|max:100',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $department                 = new Department();
            $department->branch_id      = $request->branch_id;
            $department->overtime_limit = $request->overtime_limit;
            $department->name           = $request->name;
            $department->created_by     = \Auth::user()->id;
            $department->save();

            return redirect()->route('department.index')->with('success', __('Department  successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Department $department)
    {
        return redirect()->route('department.index');
    }

    public function edit(Department $department)
    {
        if (\Auth::user()->can('Edit Department')) {
            if ($department->created_by == \Auth::user()->id || \Auth::user()->type != 'employee') {
                $branch = !empty(\Auth::user()->branch_id) ? Branch::where('id', \Auth::user()->branch_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id') : Branch::orderBy('name', 'ASC')->get()->pluck('name', 'id');

                return view('department.edit', compact('department', 'branch'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Department $department)
    {
        if (\Auth::user()->can('Edit Department')) {
            if ($department->created_by == \Auth::user()->creatorId()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'branch_id' => 'required',
                        'name' => 'required|max:100',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $department->branch_id      = $request->branch_id;
                $department->overtime_limit = $request->overtime_limit;
                $department->name           = $request->name;
                $department->save();

                return redirect()->route('department.index')->with('success', __('Department successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Department $department)
    {
        if (\Auth::user()->can('Delete Department')) {
            if ($department->created_by == \Auth::user()->id || \Auth::user()->type != 'employee') {
                $employee     = Employee::where('department_id', $department->id)->get();
                if (count($employee) == 0) {
                    Designation::where('department_id', $department->id)->delete();
                    $department->delete();
                } else {
                    return redirect()->route('department.index')->with('error', __('This department has employees. Please remove the employee from this department.'));
                }

                return redirect()->route('department.index')->with('success', __('Department successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
