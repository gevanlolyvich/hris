<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Loan;
use App\Models\LoanOption;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function loanCreate($id)
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
        
        if (empty($employee) || !$employee) {
            return redirect()->back()->with('error', __('Inactive'));
        }

        $loan_options      = LoanOption::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $loan =loan::$Loantypes;
        $recurringOptions   = [ 0 => __('No'), 1 => __('Recurring')];
        return view('loan.create', compact('employee','loan_options','loan', 'recurringOptions'));
    }

    public function store(Request $request)
    {

        if(\Auth::user()->can('Create Loan'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'employee_id' => 'required',
                                   'loan_option' => 'required',
                                   'is_recurring' => 'required',
                                   'title' => 'required',
                                   'amount' => 'required',
                                   'reason' => 'required',
                                   'period_start' => 'required_if:is_recurring,1',
                                   'period_end' => 'required_if:is_recurring,1',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            
            $employee          = Employee::find($request->employee_id);

            if (empty($employee) || !$employee) {
                return redirect()->back()->with('error', __('Inactive'));
            }    

            $loan               = new Loan();
            $loan->employee_id  = $request->employee_id;
            $loan->loan_option  = $request->loan_option;
            $loan->title        = $request->title;
            $loan->is_recurring = $request->is_recurring;
            $loan->period       = $request->is_recurring ? null : $request->period;
            $loan->period_start = $request->is_recurring ? $request->period_start : null;
            $loan->period_end   = $request->is_recurring ? $request->period_end : null;
            $loan->amount       = $request->amount;
            $loan->type         = $request->type;
            $loan->reason       = $request->reason;
            $loan->created_by   = \Auth::user()->id;
            $loan->save();

            if(  $loan->type == 'percentage' )
            {
                $loansal  = $loan->amount * $employee->salary / 100; 
            }

            return redirect()->back()->with('success', __('Loan Successfully Created'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Loan $loan)
    {
        return redirect()->route('commision.index');
    }

    public function edit($loan)
    {
        $loan = Loan::find($loan);
        if(\Auth::user()->can('Edit Loan'))
        {
            if($loan->created_by == \Auth::user()->id || \Auth::user()->type != 'employee')
            {
                $loan_options = LoanOption::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $loans =loan::$Loantypes;
                $recurringOptions   = [ 0 => __('No'), 1 => __('Recurring')];
                return view('loan.edit', compact('loan', 'loan_options','loans', 'recurringOptions'));
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

    public function update(Request $request, Loan $loan)
    {
        if(\Auth::user()->can('Edit Loan'))
        {
            if($loan->created_by == \Auth::user()->id || \Auth::user()->type != 'employee')
            {
                $validator = \Validator::make(
                    $request->all(), [

                                       'loan_option' => 'required',
                                       'title' => 'required',
                                       'amount' => 'required',
                                       'is_recurring' => 'required',
                                       'reason' => 'required',
                                       'period_start' => 'required_if:is_recurring,1',
                                       'period_end' => 'required_if:is_recurring,1',
                                   ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $employee          = Employee::find($loan->employee_id);
                if (empty($employee) || !$employee) {
                    return redirect()->back()->with('error', __('Inactive'));
                }
                
                $loan->loan_option  = $request->loan_option;
                $loan->title        = $request->title;
                $loan->is_recurring = $request->is_recurring;
                $loan->period       = $request->is_recurring ? null : $request->period;
                $loan->period_start = $request->is_recurring ? $request->period_start : null;
                $loan->period_end   = $request->is_recurring ? $request->period_end : null;
                $loan->type         = $request->type; 
                $loan->amount       = $request->amount;
                $loan->reason       = $request->reason;
                $loan->save();

                if(  $loan->type == 'percentage' )
                {
                    $loansal  = $loan->amount * $employee->salary / 100; 
                    
                }

                return redirect()->back()->with('success', __('Loan Successfully Updated'));
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

    public function destroy(Loan $loan)
    {
        if(\Auth::user()->can('Delete Loan'))
        {
            if($loan->created_by == \Auth::user()->id || \Auth::user()->type != 'employee')
            {
                $loan->delete();

                return redirect()->back()->with('success', __('Loan Successfully Deleted'));
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
