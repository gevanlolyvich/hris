<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoalController extends Controller
{

    public function index()
    {
        if(\Auth::user()->can('Manage Goal'))
        {
            if (\Auth::user()->type == 'employee') {
                $goals      = Goal::where('employee_id', \Auth::user()?->employee?->id)->orWhere('employee_id', null)->get();
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

                $employees  = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->where('is_active', 1)->select('id')->get()->pluck('id') : Employee::where('is_active', 1)->select('id')->get()->pluck('id');
                $goals      = Goal::whereIn('employee_id', $employees)->orWhere('employee_id', null)->get();
            }
            return view('goal.index', compact('goals'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Goal')) {
            if (\Auth::user()->type == 'employee') {
                $employees = Employee::where('is_active', 1)->where('user_id', '=', \Auth::user()->id)->orderby('name', 'asc')->get()->pluck('name', 'id');
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

                $employees = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id') : Employee::where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id');
            }

            return view('goal.create', compact('employees'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('Create Goal'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name'       => 'required',
                                   'start_date' => 'required|date',
                                   'end_date'   => 'required|date|after_or_equal:start_date',
                                   'target'     => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $goal               = new Goal();
            $goal->name         = $request->name;
            $goal->employee_id  = $request->employee_id;
            $goal->start_date   = $request->start_date;
            $goal->end_date     = $request->end_date;
            $goal->target       = $request->target;
            $goal->description  = $request->description;
            $goal->save();

            return redirect()->back()->with('success', __('Goal Successfully Created'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show(Goal $goal)
    {
        return view('goal.show', compact('goal'));
    }

    public function edit(Goal $goal)
    {
        if(\Auth::user()->can('Edit Goal'))
        {
            if (\Auth::user()->type == 'employee') {
                $employees = Employee::where('is_active', 1)->where('user_id', '=', \Auth::user()->id)->orderby('name', 'asc')->get()->pluck('name', 'id');
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

                $employees = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id') : Employee::where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id');
            }
            return view('goal.edit', compact('goal', 'employees'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, Goal $goal)
    {
        if(\Auth::user()->can('Edit Goal'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name'       => 'required',
                                   'start_date' => 'required|date',
                                   'end_date'   => 'required|date|after_or_equal:start_date',
                                   'target'     => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $goal->name         = $request->name;
            $goal->employee_id  = $request->employee_id;
            $goal->start_date   = $request->start_date;
            $goal->end_date     = $request->end_date;
            $goal->target       = $request->target;
            $goal->description  = $request->description;
            $goal->save();

            return redirect()->back()->with('success', __('Goal Successfully Updated'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Goal $goal)
    {
        if (\Auth::user()->can('Delete Goal')) {
            $goal->delete();

            return redirect()->back()->with('success', __('Goal Successfully Deleted'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getProgress($id)
    {
        if(\Auth::user()->can('Progress Goal'))
        {
            $goal       = Goal::find($id);
            return view('goal.progress', compact('goal'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function progress(Request $request)
    {
        if(\Auth::user()->can('Progress Goal'))
        {
            $goal           = Goal::find($request->goal_id);
            $goal->goal     = $request->goal;
            $goal->progress = $request->progress;
            $goal->save();
            return redirect()->back()->with('success', __('Goal Successfully Progressed'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
