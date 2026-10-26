<?php

namespace App\Http\Controllers;

use App\Models\Bpjs;
use App\Models\BpjsOption;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Http\Request;

class BpjsController extends Controller
{
    public function bpjsCreate($id)
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

        $bpjs_options   = BpjsOption::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $recurringOptions = [0 => __('No'), 1 => __('Recurring'), 2 => __('Prorated')];
        $bpjsTypes        = [ 'percentage' => 'Percentage'];
        return view('bpjs.create', compact('employee', 'bpjs_options', 'recurringOptions', 'bpjsTypes'));
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Bpjs')) {
            $validator = \Validator::make(
                $request->all(), [
                                   'employee_id' => 'required',
                                   'bpjs_option' => 'required',
                                   'is_recurring' => 'required|in:0,1,2',
                                   'type' => 'required',
                                   'amount' => 'required',
                               ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $employee = Employee::find($request->employee_id);

            if (empty($employee) || !$employee) {
                return redirect()->back()->with('error', __('Inactive'));
            }

            $recurringType = $request->is_recurring;

            $bpjs                = new Bpjs();
            $bpjs->employee_id   = $request->employee_id;
            $bpjs->bpjs_option   = $request->bpjs_option;
            $bpjs->is_recurring  = $recurringType == 2 ? 1 : $recurringType;
            $bpjs->is_prorated   = $recurringType == 2 ? 1 : 0;
            $bpjs->type          = $request->type;
            $bpjs->amount        = $request->amount;
            $bpjs->created_by    = \Auth::user()->id;
            $bpjs->save();

            return redirect()->back()->with('success', __('Bpjs Successfully Created'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Bpjs $bpjs)
    {
        return redirect()->route('commision.index');
    }

    public function edit($bpjs)
    {
        $bpjs = Bpjs::find($bpjs);
        if (\Auth::user()->can('Edit Bpjs')) {
            if ($bpjs->created_by == \Auth::user()->id || \Auth::user()->type != 'employee') {
                $bpjs_options = BpjsOption::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $recurringOptions   = [0 => __('No'), 1 => __('Recurring'), 2 => __('Prorated')];
                $selectedRecurring  = $bpjs->is_prorated ? 2 : ($bpjs->is_recurring ? 1 : 0);
                $bpjsTypes          = [ 'percentage' => 'Percentage'];
                return view('bpjs.edit', compact('bpjs', 'bpjs_options', 'recurringOptions', 'selectedRecurring', 'bpjsTypes'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Bpjs $bpjs)
    {
        if (\Auth::user()->can('Edit Bpjs')) {
            if ($bpjs->created_by == \Auth::user()->id || \Auth::user()->type != 'employee') {
                $validator = \Validator::make(
                    $request->all(), [

                                       'bpjs_option' => 'required',
                                       'amount' => 'required',
                                       'is_recurring' => 'required|in:0,1,2',
                                       'type' => 'required',
                                   ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $employee = Employee::find($bpjs->employee_id);
                if (empty($employee) || !$employee) {
                    return redirect()->back()->with('error', __('Inactive'));
                }

                $recurringType = $request->is_recurring;

                $bpjs->bpjs_option   = $request->bpjs_option;
                $bpjs->is_recurring  = $recurringType == 2 ? 1 : $recurringType;
                $bpjs->is_prorated   = $recurringType == 2 ? 1 : 0;
                $bpjs->type          = $request->type;
                $bpjs->amount        = $request->amount;
                $bpjs->save();

                return redirect()->back()->with('success', __('Bpjs Successfully Updated'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Bpjs $bpjs)
    {
        if (\Auth::user()->can('Delete Bpjs')) {
            if ($bpjs->created_by == \Auth::user()->id || \Auth::user()->type != 'employee') {
                $bpjs->delete();

                return redirect()->back()->with('success', __('Bpjs Successfully Deleted'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
