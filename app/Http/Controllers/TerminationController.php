<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Mail\TerminationSend;
use App\Models\Termination;
use App\Models\TerminationType;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TerminationController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('Manage Termination')) {
            if (Auth::user()->type == 'employee') {
                $emp          = Employee::where('user_id', '=', \Auth::user()->id)->first();
                $terminations = Termination::where('employee_id', '=', $emp->id)->orderBy('termination_date', 'DESC')->get();
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

                $terminations = $branch_id?->isNotEmpty() ? Termination::whereHas('employee', function ($query) use ($branch_id) { $query->whereIn('branch_id', $branch_id); })->orderBy('termination_date', 'DESC')->get() : Termination::orderBy('termination_date', 'DESC')->get();
            }

            return view('termination.index', compact('terminations'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Termination')) {
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

            $employees        = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id') : Employee::where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id');
            $terminationtypes = TerminationType::get()->pluck('name', 'id');

            return view('termination.create', compact('employees', 'terminationtypes'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Termination')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'termination_type' => 'required',
                    'notice_date' => 'required',
                    'termination_date' => 'required|after_or_equal:notice_date',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $termination                   = new Termination();
            $termination->employee_id      = $request->employee_id;
            $termination->termination_type = $request->termination_type;
            $termination->notice_date      = $request->notice_date;
            $termination->termination_date = $request->termination_date;
            $termination->description      = $request->description;
            $termination->created_by       = \Auth::user()->creatorId();
            $termination->save();

            $setings = Utility::settings();
            if ($setings['employee_termination'] == 1) {
                $employee           = Employee::find($termination->employee_id);

                $employee->terminated_by = \Auth::user()->creatorId();
                $employee->save();

                $uArr = [
                    'employee_termination_name' => $employee->name,
                    'notice_date' => $request->notice_date,
                    'termination_date' => $request->termination_date,
                    'termination_type' => $request->termination_type,
                ];
                $resp = Utility::sendEmailTemplate('employee_termination', [$employee->email], $uArr);
                return redirect()->route('termination.index')->with('success', __('Termination  successfully created.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }

            return redirect()->route('termination.index')->with('success', __('Termination  successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Termination $termination)
    {
        return redirect()->route('termination.index');
    }

    public function edit(Termination $termination)
    {
        if (\Auth::user()->can('Edit Termination')) {
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

            // $employees = Employee::where(function ($query) {
            //     // Get all active employees
            //     $query->where('is_active', 1);
            // })->orWhere(function ($query) use ($termination) {
            //     // Get the current employee from termination data
            //     $query->where('id', $termination->employee_id);
            // })->orderby('name', 'asc');

            $employees        = $branch_id?->isNotEmpty() ? Employee::where('is_active', 1)->whereIn('branch_id', $branch_id)->orWhere('id', $termination->employee_id) : Employee::where('is_active', 1)->orWhere('id', $termination->employee_id);
            $employees        = $employees->orderBy('name', 'asc')->get()->pluck('name', 'id');

            $terminationtypes = TerminationType::get()->pluck('name', 'id');

            if ($termination->created_by == \Auth::user()->creatorId()) {

                return view('termination.edit', compact('termination', 'employees', 'terminationtypes'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Termination $termination)
    {
        if (\Auth::user()->can('Edit Termination')) {
            if ($termination->created_by == \Auth::user()->creatorId()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'employee_id' => 'required',
                        'termination_type' => 'required',
                        'notice_date' => 'required',
                        'termination_date' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                if ($termination->employee_id != $request->employee_id) {
                    Employee::where('id', $termination?->employee_id)->where('is_active', 0)->update(['is_active' => 1]);
                }

                $termination->employee_id      = $request->employee_id;
                $termination->termination_type = $request->termination_type;
                $termination->notice_date      = $request->notice_date;
                $termination->termination_date = $request->termination_date;
                $termination->description      = $request->description;
                $termination->save();

                return redirect()->route('termination.index')->with('success', __('Termination successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Termination $termination)
    {
        if (\Auth::user()->can('Delete Termination')) {
            if ($termination->created_by == \Auth::user()->creatorId()) {
                Employee::where('id', $termination?->employee_id)->where('is_active', 0)->update(['is_active' => 1]);
                $termination->delete();

                return redirect()->back()->with('success', __('Termination successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function description($id)
    {
        $termination = Termination::find($id);

        return view('termination.description', compact('termination'));
    }
}
