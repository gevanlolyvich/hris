<?php

namespace App\Http\Controllers;

use App\Exports\LeaveExport;
use App\Models\AttendanceEmployee;
use App\Models\AttendanceRequest;
use App\Models\AttendanceStatus;
use App\Models\Employee;
use App\Models\ShiftTime;
use App\Models\ShiftType;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

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

        $date = date_create($request->date);
        //* Role Validation
        $employee = Employee::where('user_id', Auth::user()->id)->first();

        if (Auth::user()->type == 'employee') {
            $employee_id = $employee->id;
        } else {
            $employee_id = $request->employee_id;
        }

        $attendance = AttendanceEmployee::where('employee_id', $employee_id)->where('date', $date)->first();
        if ($attendance) {
            return redirect()->back()->with('error', __('You were present on that date already'));
        }

        //* Custom Form data
        $employee = Employee::find($employee_id);
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

    public function update(Request $request, $attendance_request_id)
    {
        $attendance_request = AttendanceRequest::find($attendance_request_id);
        if (Auth::user()->can('Edit Leave')) {
            if ($attendance_request->created_by == Auth::user()->creatorId()) {
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

                //* Custom Form
                $date = date_create($request->date);
                $document_path = null;
                if ($request->file('document')) {
                    $docs = $request->file('document');
                    $docName = time() . "_" . date_format($date, "Y-m-d") . "_" . preg_replace('/\s+/', '', $attendance_request->employee->name) . "." . $docs->getClientOriginalExtension();
                    $path = $docs->storeAs('uploads/attendance_requests', $docName, 'public');
                    $document_path = env('APP_URL') . '/storage/' . $path;
                }

                //* Input Data
                $form = [
                    'date'          => date_format($date, "Y-m-d"),
                    'start_time'    => $request->start_time,
                    'end_time'      => $request->end_time,
                    'reason'        => $request->reason,
                    'docs'          => $document_path ? $document_path : $attendance_request->docs,
                ];

                //* Update Data
                AttendanceRequest::where('id', $attendance_request->id)->update($form);
                return redirect()->route('attendancerequest.index')->with('success', __('Attendance Request Successfully Updated'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy($attendance_request_id)
    {
        $attendance_request = AttendanceRequest::find($attendance_request_id);
        if (Auth::user()->can('Delete Leave')) {
            if ($attendance_request->created_by == Auth::user()->creatorId()) {
                $attendance_request->delete();
                return redirect()->route('attendancerequest.index')->with('success', __('Attendance Request Successfully Deleted'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function action($id)
    {
        // return $id;
        $attendance_request     = AttendanceRequest::find($id);
        $employee  = Employee::find($attendance_request->employee_id);
        // $leavetype = LeaveType::find($leave->leave_type_id);

        return view('attendancerequest.action', compact('employee', 'attendance_request'));
    }

    public function changeaction(Request $request)
    {
        $presentAttendance = AttendanceStatus::where('id', 1)->first();
        $attendance_request = AttendanceRequest::find($request->attendance_request_id);
        $date = $attendance_request->date;

        if ($request->status == 'Approved') {
            $form = [
                'is_approved'   => true,
                'approved_by'   => Auth::user()->id
            ];
        } elseif ($request->status == 'Reject') {
            $form = [
                'is_approved'   => false,
                'approved_by'   => Auth::user()->id
            ];
        }

        if ($form['is_approved']) {
            //* Check availability attendance
            $attendance = AttendanceEmployee::where('employee_id', $attendance_request->employee->id)->where('date', $date)->first();
            if ($attendance) {
                return redirect()->back()->with('error', __('You were present on that date already'));
            }

            //* Method Create Attendance
            $shift_times = ShiftTime::where('shift_type_id', $attendance_request->employee->shift_type->id)
                ->where('days', date('l'))
                ->first();


            if ($shift_times->is_working) {
                $startTime = $shift_times->start_time;
                $endTime = $shift_times->end_time;

                $totalLateSeconds = strtotime($date . $attendance_request->end_time) - strtotime($date . $startTime);

                $hours = floor($totalLateSeconds / 3600);
                $mins  = floor($totalLateSeconds / 60 % 60);
                $secs  = floor($totalLateSeconds % 60);
                $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                //work hours
                $totalWorkHoursSeconds    = strtotime($date . $attendance_request->end_time) - strtotime($date . $attendance_request->start_time);
                $hours                    = floor($totalWorkHoursSeconds / 3600);
                $mins                     = floor($totalWorkHoursSeconds / 60 % 60);
                $secs                     = floor($totalWorkHoursSeconds % 60);
                $workHours                = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                //early Leaving
                $totalEarlyLeavingSeconds = strtotime($date . $endTime) - strtotime($date . $attendance_request->end_time);
                $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                $secs                     = floor($totalEarlyLeavingSeconds % 60);
                $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);


                if (strtotime($date . $attendance_request->end_time) > strtotime($date . $endTime)) {
                    //Overtime
                    $totalOvertimeSeconds = strtotime($date . $attendance_request->end_time) - strtotime($date . $endTime);
                    $hours                = floor($totalOvertimeSeconds / 3600);
                    $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                    $secs                 = floor($totalOvertimeSeconds % 60);
                    $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                } else {
                    $overtime = '00:00:00';
                }

                $employee = $attendance_request->employee;
                $form_attendance = [
                    'employee_id'           => $employee->id,
                    'date'                  => $date,
                    'attendance_status_id'  => $presentAttendance->id,
                    'status'                => $presentAttendance->name,
                    'clock_in'              => $attendance_request->start_time . ':00',
                    'clock_out'             => $attendance_request->end_time . ':00',
                    'late'                  => $late,
                    'early_leaving'         => $earlyLeaving,
                    'work_hours'            => $workHours,
                    'overtime'              => $overtime,
                    'total_rest'            => '00:00:00',
                    'created_by'            => $employee->user_id,
                    'attendance_type_id'    => 1, //* ON SITE
                    'coord_in'              => null,
                    'coord_out'             => null,
                    'is_valid'              => true,
                    'validate_by'           => Auth::user()->id,
                ];
            } else {
                $form_attendance = [
                    'employee_id'           => $attendance_request->employee->id,
                    'date'                  => $date,
                    'attendance_status_id'  => $presentAttendance->id,
                    'status'                => $presentAttendance->name,
                    'clock_in'              => $attendance_request->start_time . ':00',
                    'clock_out'             => $attendance_request->end_time . ':00',
                    'late'                  => '00:00:00',
                    'early_leaving'         => '00:00:00',
                    'work_hours'            => '00:00:00',
                    'overtime'              => '00:00:00',
                    'total_rest'            => '00:00:00',
                    'created_by'            => $attendance_request->employee->user_id,
                    'attendance_type_id'    => 1, //* ON SITE
                    'coord_in'              => null,
                    'coord_out'             => null,
                    'is_valid'              => true,
                    'validate_by'           => Auth::user()->id,
                ];
            }
        }

        DB::transaction(function () use ($attendance_request, $form, $form_attendance) {
            AttendanceRequest::where('id', $attendance_request->id)->update($form);
            AttendanceEmployee::create($form_attendance);
        });

        return redirect()->route('attendancerequest.index')->with('success', __('Request Attendance Successfully Updated'));
    }

    public function export(Request $request)
    {
        $name = 'Leave' . date('Y-m-d i:h:s');
        $data = Excel::download(new LeaveExport(), $name . '.xlsx');

        return $data;
    }
}
