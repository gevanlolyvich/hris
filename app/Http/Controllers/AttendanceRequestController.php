<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRequest;
use App\Models\Employee;
use App\Models\ShiftType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AttendanceRequestController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('Manage Leave')) {
            $attendance_requests = AttendanceRequest::where('created_by', '=', Auth::user()->creatorId())->get();
            if (Auth::user()->type == 'employee') {
                $user     = Auth::user();
                $employee = Employee::where('user_id', '=', $user->id)->first();
                $attendance_requests   = AttendanceRequest::where('employee_id', '=', $employee->id)->get();
            } else {
                $attendance_requests = AttendanceRequest::orderBy('id', 'DESC')->get();
            }
            // return $attendance_requests;
            return view('attendancerequest.index', compact('attendance_requests'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (Auth::user()->can('Create Leave')) {
            if (Auth::user()->type == 'employee') {
                $employees = Employee::where('user_id', '=', Auth::user()->id)->get()->pluck('name', 'id');
            } else {
                $employees = Employee::where('created_by', '=', Auth::user()->creatorId())->get()->pluck('name', 'id');
            }
            // $leavetypes      = LeaveType::where('created_by', '=', Auth::user()->creatorId())->get();
            // $leavetypes_days = LeaveType::where('created_by', '=', Auth::user()->creatorId())->get();

            return view('attendancerequest.create', compact('employees'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        //* Data Validation 
        $validator = Validator::make(
            $request->all(),
            [
                'date' => 'required|before:today',
                'start_time' => 'required',
                'end_time' => 'required',
                'reason' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        //* Role Validation
        $employee = Employee::where('user_id', Auth::user()->id)->first();
        if (Auth::user()->type == 'employee') {
            $employee_id = $employee->id;
        } else {
            $employee_id = $request->employee_id;
        }

        //* Custom Form data
        $employee = Employee::find($employee_id);
        $date = date_create($request->date);

        $document_path = null;
        if ($request->file('document')) {
            $docs = $request->file('document');
            $docName = time() . "_" . date_format($date, "Y-m-d") . "_" . preg_replace('/\s+/', '', $employee->name) . "." . $docs->getClientOriginalExtension();
            $path = $docs->storeAs('uploads/attendance_requests', $docName, 'public');
            $document_path = env('APP_URL') . '/storage/' . $path;
        }

        //* Input Data
        $form = [
            'employee_id'   => $employee_id,
            'date'          => date_format($date, "Y-m-d"),
            'start_time'    => $request->start_time,
            'end_time'      => $request->end_time,
            'reason'        => $request->reason,
            'docs'          => $document_path,
            'created_by'    => Auth::user()->creatorId()
        ];

        //* Input to DB
        $attendanceRequest = AttendanceRequest::create($form);
        return redirect()->route('attendancerequest.index')->with('success', __('Request Attendance Successfully Created'));
    }

    public function show(AttendanceRequest $attendance_request)
    {
        return redirect()->route('attendancerequest.index');
    }

    public function edit($id)
    {
        $attendance_request = AttendanceRequest::find($id);

        if (Auth::user()->can('Edit Leave')) {
            if ($attendance_request->created_by == Auth::user()->creatorId()) {
                $employees  = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

                return view('attendancerequest.edit', compact('employees', 'attendance_request'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request)
    {
        return $request;
    }

    public function destroy(AttendanceRequest $attendanceReq)
    {
        if (\Auth::user()->can('Delete Leave')) {
            $attendanceReq->delete();
            return redirect()->route('attendancerequest.index')->with('success', __('Leave successfully deleted.'));
            if ($attendanceReq->created_by == \Auth::user()->creatorId()) {

                return redirect()->route('attendancerequest.index')->with('success', __('Leave successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
