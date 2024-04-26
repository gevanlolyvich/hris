<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Commission;
use App\Models\Employee;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function commissionCreate($id)
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

        $employee = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->where('is_active', 1)->find($id) : Employee::where('is_active', 1)->find($id);
        $commissions =Commission::$commissiontype;
        $recurringOptions   = [ 0 => __('No'), 1 => __('Recurring')];

        return view('commission.create', compact('employee','commissions', 'recurringOptions'));
    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('Create Commission'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'employee_id' => 'required',
                                   'title' => 'required',
                                   'is_recurring' => 'required',
                                   'amount' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $employee          = Employee::where('is_active', 1)->find($request->employee_id);

            if (empty($employee) || !$employee) {
                return redirect()->back()->with('error', __('Inactive'));
            }

            $commission                 = new Commission();
            $commission->employee_id    = $request->employee_id;
            $commission->title          = $request->title;
            $commission->is_recurring   = $request->is_recurring;
            $commission->period         = $request->period;
            $commission->type           = $request->type;
            $commission->amount         = $request->amount;
            $commission->created_by     = \Auth::user()->id;
            $commission->save();

            if(  $commission->type == 'percentage' )
            {
                $comsal            = $commission->amount * $employee->salary / 100; 
            }

            return redirect()->back()->with('success', __('Commission Successfully Created'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Commission $commission)
    {
        return redirect()->route('commision.index');
    }

    public function edit($commission)
    {
        $commission = Commission::find($commission);
        if(\Auth::user()->can('Edit Commission'))
        {
            if($commission->created_by == \Auth::user()->id || \Auth::user()->type != 'employee')
            {
                $commissions =Commission::$commissiontype;
                $recurringOptions   = [ 0 => __('No'), 1 => __('Recurring')];
                return view('commission.edit', compact('commission','commissions', 'recurringOptions'));
            }
            else
            {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Commission $commission)
    {
        if(\Auth::user()->can('Edit Commission'))
        {
            if($commission->created_by == \Auth::user()->id || \Auth::user()->type != 'employee')
            {
                $validator = \Validator::make(
                    $request->all(), [

                                       'title' => 'required',
                                       'date' => 'required',
                                       'amount' => 'required',
                                   ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $commission->title  = $request->title;
                $commission->date   = $request->date;
                $commission->type   = $request->type;
                $commission->amount = $request->amount;
                $commission->save();

                return redirect()->back()->with('success', __('Commission successfully updated.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Commission $commission)
    {

        if(\Auth::user()->can('Delete Commission'))
        {
            if($commission->created_by == \Auth::user()->id || \Auth::user()->type != 'employee')
            {
                $commission->delete();

                return redirect()->back()->with('success', __('Commission successfully deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
