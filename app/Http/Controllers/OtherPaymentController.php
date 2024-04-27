<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\OtherPayment;
use Illuminate\Http\Request;

class OtherPaymentController extends Controller
{
    public function otherpaymentCreate($id)
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
        $otherpaytype=OtherPayment::$otherPaymenttype;
        $recurringOptions = [ 0 => __('No'), 1 => __('Recurring')];
        return view('otherpayment.create', compact('employee','otherpaytype', 'recurringOptions'));
    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('Create Other Payment'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'employee_id' => 'required',
                                   'is_recurring' => 'required',
                                   'title' => 'required',
                                   'amount' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $employee          = Employee::where('is_active', 1)->find($request->employee_id);

            if(empty($employee) || !$employee) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }

            $otherpayment               = new OtherPayment();
            $otherpayment->employee_id  = $request->employee_id;
            $otherpayment->title        = $request->title;
            $otherpayment->is_recurring = $request->is_recurring;
            $otherpayment->period       = $request->period;
            $otherpayment->type         = $request->type;
            $otherpayment->amount       = $request->amount;
            $otherpayment->created_by   = \Auth::user()->id;
            $otherpayment->save();

            if(  $otherpayment->type == 'percentage' )
            {
                $loansal  = $otherpayment->amount * $employee->salary / 100; 
                
            }  

            return redirect()->back()->with('success', __('Other Payment Successfully Created'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(OtherPayment $otherpayment)
    {
        return redirect()->route('commision.index');
    }

    public function edit($otherpayment)
    {
        $otherpayment = OtherPayment::find($otherpayment);
        if(\Auth::user()->can('Edit Other Payment'))
        {
            if($otherpayment->created_by == \Auth::user()->creatorId())
            {    
                $otherpaytypes=OtherPayment::$otherPaymenttype;
                return view('otherpayment.edit', compact('otherpayment','otherpaytypes'));
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

    public function update(Request $request, OtherPayment $otherpayment)
    {
        if(\Auth::user()->can('Edit Other Payment'))
        {
            if($otherpayment->created_by == \Auth::user()->creatorId())
            {
                $validator = \Validator::make(
                    $request->all(), [

                                       'title' => 'required',
                                       'amount' => 'required',
                                   ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $employee          = Employee::where('is_active', 1)->find($otherpayment->employee_id);

                if(empty($employee) || !$employee) {
                    return redirect()->back()->with('error', __('Permission denied.'));
                }

                $otherpayment->title  = $request->title;
                $otherpayment->type   = $request->type;
                $otherpayment->amount = $request->amount;
                $otherpayment->save();

                if(  $otherpayment->type == 'percentage' )
                {
                    $loansal  = $otherpayment->amount * $employee->salary / 100; 
                    
                }

                return redirect()->back()->with('success', __('OtherPayment successfully updated.'));
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

    public function destroy(OtherPayment $otherpayment)
    {
        if(\Auth::user()->can('Delete Other Payment'))
        {
            if($otherpayment->created_by == \Auth::user()->creatorId())
            {
                $otherpayment->delete();

                return redirect()->back()->with('success', __('OtherPayment successfully deleted.'));
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
