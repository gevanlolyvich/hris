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
        return view('loan.create', compact('employee','loan_options','loan'));
    }

    public function store(Request $request)
    {

        if(\Auth::user()->can('Create Loan'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'employee_id' => 'required',
                                   'loan_option' => 'required',
                                   'title' => 'required',
                                   'amount' => 'required',
                                   'start_date' => 'required',
                                   'end_date' => 'required',
                                   'reason' => 'required',
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

            $loan              = new Loan();
            $loan->employee_id = $request->employee_id;
            $loan->loan_option = $request->loan_option;
            $loan->title       = $request->title;
            $loan->amount      = $request->amount;
            $loan->type        = $request->type;
            $loan->start_date  = $request->start_date;
            $loan->end_date    = $request->end_date;
            $loan->reason      = $request->reason;
            $loan->created_by  = \Auth::user()->creatorId();
            $loan->save();

            if(  $loan->type == 'percentage' )
            {
                $loansal  = $loan->amount * $employee->salary / 100; 
            }

            return redirect()->back()->with('success', __('Loan  successfully created.'));
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
            if($loan->created_by == \Auth::user()->creatorId())
            {
                $loan_options = LoanOption::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $loans =loan::$Loantypes;
                return view('loan.edit', compact('loan', 'loan_options','loans'));
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
            if($loan->created_by == \Auth::user()->creatorId())
            {
                $validator = \Validator::make(
                    $request->all(), [

                                       'loan_option' => 'required',
                                       'title' => 'required',
                                       'amount' => 'required',
                                       'start_date' => 'required',
                                       'end_date' => 'required',
                                       'reason' => 'required',
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
                
                $loan->loan_option = $request->loan_option;
                $loan->title       = $request->title;
                $loan->type        = $request->type; 
                $loan->amount      = $request->amount;
                $loan->start_date  = $request->start_date;
                $loan->end_date    = $request->end_date;
                $loan->reason      = $request->reason;
                $loan->save();

                if(  $loan->type == 'percentage' )
                {
                    $loansal  = $loan->amount * $employee->salary / 100; 
                    
                }

                return redirect()->back()->with('success', __('Loan successfully updated.'));
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
            if($loan->created_by == \Auth::user()->creatorId())
            {
                $loan->delete();

                return redirect()->back()->with('success', __('Loan successfully deleted.'));
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
