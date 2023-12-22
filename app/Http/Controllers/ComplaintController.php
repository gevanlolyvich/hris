<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Employee;
use App\Mail\ComplaintsSend;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ComplaintController extends Controller
{
    public function index()
    {
        if(\Auth::user()->can('Manage Complaint'))
        {
            if(Auth::user()->type == 'employee')
            {
                $emp        = Employee::where('user_id', '=', \Auth::user()->id)->first();
                $complaints = Complaint::where('complaint_from', '=', $emp->id)->get();
            }
            else
            {
                $complaints = Complaint::where('created_by', '=', \Auth::user()->creatorId())->get();
            }

            return view('complaint.index', compact('complaints'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->can('Create Complaint'))
        {
            if(Auth::user()->type == 'employee')
            {
                $user             = \Auth::user();
                $current_employee = Employee::where('is_active', 1)->where('user_id', $user->id)->get()->pluck('name', 'id');
                $employees        = Employee::where('is_active', 1)->where('user_id', '!=', $user->id)->orderby('name', 'asc')->get()->pluck('name', 'id');

            }
            else
            {
                $user             = \Auth::user();
                $current_employee = Employee::where('is_active', 1)->where('user_id', $user->id)->get()->pluck('name', 'id');
                $employees        = Employee::where('is_active', 1)->where('created_by', Auth::user()->created_by)->orderby('name', 'asc')->get()->pluck('name', 'id');
            }


            return view('complaint.create', compact('employees', 'current_employee'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('Create Complaint'))
        {
            if(\Auth::user()->type != 'employee')
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'complaint_from' => 'required',
                                   ]
                );
            }

            $validator = \Validator::make(
                $request->all(), [
                                   'complaint_against' => 'required',
                                   'title' => 'required',
                                   'complaint_date' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $complaint = new Complaint();
            if(\Auth::user()->type == 'employee')
            {
                $emp                       = Employee::where('is_active', 1)->where('user_id', '=', \Auth::user()->id)->first();
                if (empty($emp) || !$emp) {
                    return redirect()->back()->with('error', __('Inactive'));
                }

                $complaint->complaint_from = $emp->id;
            }
            else
            {
                $complaint->complaint_from = $request->complaint_from;
            }
            $complaint->complaint_against = $request->complaint_against;
            $complaint->title             = $request->title;
            $complaint->complaint_date    = $request->complaint_date;
            $complaint->description       = $request->description;
            $complaint->created_by        = \Auth::user()->creatorId();
            $complaint->save();

            $setings = Utility::settings();
            if($setings['employee_complaints'] == 1)
            {
                $employee         = Employee::where('is_active', 1)->find($complaint->complaint_against);
                if (empty($emp) || !$emp) {
                    return redirect()->back()->with('error', __('Inactive'));
                }

                $uArr = ['employee_complaints_name'=>$employee->name];
                $resp = Utility::sendEmailTemplate('employee_complaints', [$employee->email], $uArr);
                return redirect()->route('complaint.index')->with('success', __('Complaint  successfully created.'). ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }

            return redirect()->route('complaint.index')->with('success', __('Complaint  successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Complaint $complaint)
    {
        return redirect()->route('complaint.index');
    }

    public function edit($complaint)
    {
        $complaint = Complaint::find($complaint);
        if(\Auth::user()->can('Edit Complaint'))
        {
            if(Auth::user()->type == 'employee')
            {
                $user             = \Auth::user();
                $current_employee = Employee::where('is_active', 1)->where('user_id', $user->id)->get()->pluck('name', 'id');
                $employees        = Employee::where('is_active', 1)->where('user_id', '!=', $user->id)->orderby('name', 'asc')->get()->pluck('name', 'id');
            }
            else
            {
                $user             = \Auth::user();
                $current_employee = Employee::where('is_active', 1)->where('user_id', $user->id)->get()->pluck('name', 'id');
                $employees        = Employee::where('is_active', 1)->where('created_by', \Auth::user()->creatorId())->orderby('name', 'asc')->get()->pluck('name', 'id');
            }
            if($complaint->created_by == \Auth::user()->creatorId())
            {
                return view('complaint.edit', compact('complaint', 'employees', 'current_employee'));
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

    public function update(Request $request, Complaint $complaint)
    {
        if(\Auth::user()->can('Edit Complaint'))
        {
            if($complaint->created_by == \Auth::user()->creatorId())
            {
                if(\Auth::user()->type != 'employee')
                {
                    $validator = \Validator::make(
                        $request->all(), [
                                           'complaint_from' => 'required',
                                       ]
                    );
                }

                $validator = \Validator::make(
                    $request->all(), [

                                       'complaint_against' => 'required',
                                       'title' => 'required',
                                       'complaint_date' => 'required',
                                   ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                if(\Auth::user()->type == 'employee')
                {
                    $emp                       = Employee::where('is_active', 1)->where('user_id', '=', \Auth::user()->id)->first();
                    if (empty($emp) || !$emp) {
                        return redirect()->back()->with('error', __('Inactive'));
                    }
                    $complaint->complaint_from = $emp->id;
                }
                else
                {
                    $complaint->complaint_from = $request->complaint_from;
                }
                $complaint->complaint_against = $request->complaint_against;
                $complaint->title             = $request->title;
                $complaint->complaint_date    = $request->complaint_date;
                $complaint->description       = $request->description;
                $complaint->save();

                return redirect()->route('complaint.index')->with('success', __('Complaint successfully updated.'));
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

    public function destroy(Complaint $complaint)
    {
        if(\Auth::user()->can('Delete Complaint'))
        {
            if($complaint->created_by == \Auth::user()->creatorId())
            {
                $complaint->delete();

                return redirect()->route('complaint.index')->with('success', __('Complaint successfully deleted.'));
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
