<?php

namespace App\Http\Controllers;

use App\Models\Allowance;
use App\Models\AllowanceOption;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Http\Request;

class AllowanceController extends Controller
{
    public function allowanceCreate($id)
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

        $allowance_options  = AllowanceOption::get()->pluck('name', 'id');
        $employee           = $branch_id?->isNotEmpty() ? Employee::where('is_active', 1)->whereIn('branch_id', $branch_id)->find($id) : Employee::where('is_active', 1)->find($id);
        $recurringOptions   = [0 => __('No'), 1 => __('Recurring')];

        return view('allowance.create', compact('employee', 'allowance_options', 'recurringOptions'));
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Allowance')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'allowance_option' => 'required',
                    'is_recurring' => 'required',
                    'title' => 'required',
                    'amount' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $allowance                      = new Allowance();
            $allowance->employee_id         = $request->employee_id;
            $allowance->allowance_option    = $request->allowance_option;
            $allowance->title               = $request->title;
            $allowance->is_recurring        = $request->is_recurring;
            $allowance->period              = $request->period;
            $allowance->amount              = $request->amount;
            $allowance->created_by          = \Auth::user()->id;
            $allowance->save();

            return redirect()->back()->with('success', __('Allowance Successfully Created'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Allowance $allowance)
    {
        return redirect()->route('allowance.index');
    }

    public function edit($allowance)
    {
        $allowance = Allowance::find($allowance);
        if (\Auth::user()->can('Edit Allowance')) {
            $allowance_options = AllowanceOption::get()->pluck('name', 'id');
            $recurringOptions   = [0 => __('No'), 1 => __('Recurring')];

            return view('allowance.edit', compact('allowance', 'allowance_options', 'recurringOptions'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Allowance $allowance)
    {
        if (\Auth::user()->can('Edit Allowance')) {
            if ($allowance->created_by == \Auth::user()->id || \Auth::user()->type != 'employee') {
                $validator = \Validator::make(
                    $request->all(),
                    [

                        'allowance_option' => 'required',
                        'is_recurring' => 'required',
                        'title' => 'required',
                        'amount' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $allowance->allowance_option    = $request->allowance_option;
                $allowance->title               = $request->title;
                $allowance->is_recurring        = $request->is_recurring;
                $allowance->period              = $request->period;
                $allowance->amount              = $request->amount;
                $allowance->save();

                return redirect()->back()->with('success', __('Allowance Successfully Updated'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Allowance $allowance)
    {

        if (\Auth::user()->can('Delete Allowance')) {
            if ($allowance->created_by == \Auth::user()->id || \Auth::user()->type != 'employee') {
                $allowance->delete();

                return redirect()->back()->with('success', __('Allowance Successfully Deleted'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
