<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BranchController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('Manage Branch')) {
            $branches = !empty(\Auth::user()->branch_id) ? Branch::where('id', \Auth::user()->branch_id)->orderBy('id', 'ASC')->get() : Branch::orderBy('id', 'ASC')->get();

            return view('branch.index', compact('branches'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Branch')) {
            $parent_branches = Branch::orderBy('name', 'ASC')->get()->pluck('name', 'id');

            return view('branch.create', compact('parent_branches'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Branch')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'tolerance' => 'required',
                    'latitude' => 'required',
                    'longitude' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $branch                 = new Branch();
            $branch->name           = $request->name;
            $branch->parent_branch  = $request->parent_branch;
            $branch->tolerance      = $request->tolerance;
            $branch->latitude       = $request->latitude;
            $branch->longitude      = $request->longitude;
            $branch->created_by     = \Auth::user()->id;
            $branch->save();

            return redirect()->route('branch.index')->with('success', __('Branch  successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Branch $branch)
    {
        return redirect()->route('branch.index');
    }

    public function edit(Branch $branch)
    {
        if (\Auth::user()->can('Edit Branch')) {
            $parent_branches = Branch::whereNot('id', $branch->id)->orderBy('name', 'ASC')->get()->pluck('name', 'id');

            return view('branch.edit', compact('branch', 'parent_branches'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Branch $branch)
    {
        if (\Auth::user()->can('Edit Branch')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'tolerance' => 'required',
                    'latitude' => 'required',
                    'longitude' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $branch->name           = $request->name;
            $branch->tolerance      = $request->tolerance;
            $branch->parent_branch  = $request->parent_branch;
            $branch->latitude       = $request->latitude;
            $branch->longitude      = $request->longitude;
            $branch->save();

            return redirect()->route('branch.index')->with('success', __('Branch successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Branch $branch)
    {
        if (\Auth::user()->can('Delete Branch')) {
            if ($branch->created_by == \Auth::user()->creatorId()) {
                $employee     = Employee::where('branch_id', $branch->id)->get();
                if (count($employee) == 0) {
                    $department = Department::where('branch_id', $branch->id)->first();
                    if (!empty($department)) {
                        Designation::where('department_id', $department->branch_id)->delete();
                        $department->delete();
                    }
                    $branch->delete();
                } else {
                    return redirect()->route('branch.index')->with('error', __('This branch has employees. Please remove the employee from this branch.'));
                }
                return redirect()->route('branch.index')->with('success', __('Branch successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getdepartment(Request $request)
    {

        if ($request->branch_id == 0) {
            $departments = Department::get()->pluck('name', 'id')->toArray();
        } else {
            $departments = Department::where('branch_id', $request->branch_id)->get()->pluck('name', 'id')->toArray();
        }

        return response()->json($departments);
    }

    public function getemployee(Request $request)
    {
        if (in_array('0', $request->department_id)) {
            $employees = Employee::where('is_active', 1)->orderby('name', 'asc')->orderby('name', 'asc')->get()->pluck('name', 'id')->toArray();
        } else {
            $employees = Employee::where('is_active', 1)->whereIn('department_id', $request->department_id)->orderby('name', 'asc')->orderby('name', 'asc')->get()->pluck('name', 'id')->toArray();
        }

        return response()->json($employees);
    }
}
