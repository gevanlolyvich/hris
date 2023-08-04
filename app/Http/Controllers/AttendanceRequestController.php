<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRequest;
use App\Models\Employee;
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
                $attendance_requests = AttendanceRequest::where('created_by', '=', Auth::user()->creatorId())->get();
            }

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
        $employee = Employee::find($employee_id);
        $date = date_create($request->date);
        // return date_format($date, "Y-m-d") . "_" . preg_replace('/\s+/', '', $employee->name);

        $document_path = null;
        if ($request->file('document')) {
            $docs = $request->file('document');
            $docName = time() . "_" . date_format($date, "Y-m-d") . "_" . preg_replace('/\s+/', '', $employee->name) . "." . $docs->getClientOriginalExtension();
            $path = $docs->storeAs('uploads/attendance_requests', $docName, 'public');
            $document_path = env('APP_URL') . '/storage/' . $path;
            // return "ada document";
        }
        // return "tidak ada document";

        // $path = Storage::put('attendance_requests', $request->file('document'));
        // $visibility = Storage::getVisibility('attendance_requests');

        // Storage::setVisibility('attendance_requests', 'public');
        // $path = Storage::putFile('attendance_requests', $request->file('document'));

        // $file_path = storage_path($path);
        // // $file_path = $path = Storage::disk('local')->getAdapter()->applyPathPrefix($filename);
        // return $path;

        //* Input Data
        $form = [
            'employee_id'   => $employee_id,
            'date'          => date_format($date, "Y-m-d"),
            'start_time'    => $request->start_time,
            'end_time'      => $request->end_time,
            'reason'        => $request->reason,
            'docs'          => $document_path,
            'created_by'    => Auth::user()->id
        ];

        $attendanceRequest = AttendanceRequest::create($form);
        return redirect()->route('attendancerequest.index')->with('success', __('Request Attendance Successfully Created'));
    }
}
