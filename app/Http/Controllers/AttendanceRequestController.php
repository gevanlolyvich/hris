<?php

namespace App\Http\Controllers;

use App\Exports\LeaveExport;
use App\Models\AttendanceEmployee;
use App\Models\AttendanceRequest;
use App\Models\AttendanceStatus;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\Department;
use App\Models\ShiftTime;
use App\Models\ShiftType;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceRequestController extends Controller
{
    public function index(Request $request)
    {
        if (\Auth::user()->can('Manage Request Attendance')) {
            $is_approved = $request->query('is_approved', null);
            $branch = !empty(\Auth::user()->branch_id) ? Branch::where('id', \Auth::user()->branch_id)->get()->pluck('name', 'id') : Branch::get()->pluck('name', 'id');
            $department = collect();

            if (Auth::user()->type == 'employee') {
                $user     = Auth::user();

                $subordinate_ids = \Auth::user()?->employee?->subordinatesFlatten()->pluck('id')->toArray();
                $employee_id = null;
                if (!empty($subordinate_ids)) {
                    $employee_id = $subordinate_ids;
                    $employee_id[] = \Auth::user()->employee->id;
                } else {
                    $employee_id[] = \Auth::user()->employee->id;
                }

                $attendance_requests   = AttendanceRequest::wherein('employee_id', $employee_id)->orderBy('date', 'DESC')->orderBy('employee_id', 'ASC');
            } else {
                $employee_id = Employee::where('branch_id', \Auth::user()?->branch_id ?? 0)->get()->pluck('id')->toArray();
                $attendance_requests = !empty(\Auth::user()?->branch_id) ? AttendanceRequest::whereIn('employee_id', $employee_id)->orderBy('date', 'DESC')->orderBy('employee_id', 'ASC') : AttendanceRequest::orderBy('date', 'DESC')->orderBy('employee_id', 'ASC');
            }

            if ($is_approved != null && $is_approved == '0') {
                $attendance_requests->whereNull('is_approved');
            }

            if (!empty($request->branch_id)) {
                $department     = Department::where('branch_id', $request->branch_id)->get()->pluck('name', 'id');
                $attendance_requests         = $attendance_requests->whereHas('employee', function ($query) use ($request) { $query->where('branch_id', $request->branch_id); });
            }
            if (!empty($request->department_id)) {
                $department     = empty($request->branch_id) ? Department::where('department_id', $request->department_id)->get()->pluck('name', 'id') : $department;
                $attendance_requests         = $attendance_requests->whereHas('employee', function ($query) use ($request) { $query->where('department_id', $request->department_id); });
            }

            $attendance_requests = $attendance_requests->get();
            return view('attendancerequest.index', compact('attendance_requests', 'branch', 'department'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Request Attendance')) {
            if (Auth::user()->type == 'employee') {
                $employees = Employee::where('is_active', 1)->where('user_id', Auth::user()->id)->orderby('name', 'asc')->get()->pluck('name', 'id');
            } else {
                $employees = !empty(\Auth::user()?->branch_id) ? Employee::where('branch_id', \Auth::user()?->branch_id)->where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id') : Employee::where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id');
            }

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
                'myDocument' => 'required',
                'shift_id' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }
    
        $date = date_create($request->date);
        //* Role Validation
        $employee = Employee::where('is_active', 1)->where('id', $request->employee_id)->first();
        if (empty($employee) || !$employee) {
            return redirect()->back()->with('error', __('Inactive'));
        }

        $attendance = AttendanceEmployee::where('employee_id', $employee->id)->where('date', $date)->first();
        if ($attendance) {
            return redirect()->back()->with('error', __('You were present on that date already'));
        }

        $duplicate_request = AttendanceRequest::where('employee_id', $employee->id)->where('date', $date)->first();
        if ($duplicate_request) {
            return redirect()->back()->with('error', __('Duplicate Request Attendance'));
        }

        //* Custom Form data
        $employee = Employee::where('is_active', 1)->find($employee->id);
        $document_path = null;
        if ($request->file('myDocument')) {
            $docs = $request->file('myDocument');
            $docName = time() . "_" . date_format($date, "Y-m-d") . "_" . preg_replace('/\s+/', '', $employee->name) . "." . $docs->getClientOriginalExtension();
            $path = $docs->storeAs('uploads/attendance_requests', $docName, 'public');
            $document_path = env('APP_URL') . '/storage/' . $path;
        }

        //* Input Data
        $form = [
            'employee_id'   => $employee->id,
            'shift_id'      => $request->shift_id,
            'date'          => date_format($date, "Y-m-d"),
            'start_time'    => $request->start_time,
            'end_time'      => $request->end_time,
            'reason'        => $request->reason,
            'docs'          => $document_path,
            'created_by'    => Auth::user()->id,
        ];

        //* Input to DB
        AttendanceRequest::create($form);
        return redirect()->route('attendancerequest.index')->with('success', __('Request Attendance Successfully Created'));
    }

    public function show(AttendanceRequest $attendance_request)
    {
        return redirect()->route('attendancerequest.index');
    }

    public function edit($id)
    {
        $attendance_request = AttendanceRequest::find($id);

        if (\Auth::user()->can('Edit Request Attendance')) {
            if (($attendance_request->created_by == Auth::user()->id || $attendance_request->employee_id == Auth::user()->employee->id || Auth::user()->type != 'employee') && $attendance_request->is_approved != 1) {
                $employees = !empty(\Auth::user()?->branch_id) ? Employee::where('branch_id', \Auth::user()?->branch_id)->where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id') : Employee::where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id');
                $shifts    = ShiftType::where('branch_id', $attendance_request->employee->branch_id)->orderby('name', 'asc')->get()->pluck('name', 'id');

                foreach ($shifts as $key => $shift) {
                    $times = ShiftTime::where('shift_type_id', $key)->where('days', date('l', strtotime($attendance_request->date ? $attendance_request->date : date('Y-m-d'))))->select('start_time', 'end_time')->first();
                    $formated_times = !empty($times->start_time) || !empty($times->end_time) ? substr($times->start_time, 0, 5) . ' - ' . substr($times->end_time, 0, 5) : __('Holidays');
                    $shifts[$key] = $formated_times.  ' | ' . $shift;
                }

                return view('attendancerequest.edit', compact('employees', 'attendance_request', 'shifts'));
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
        if (\Auth::user()->can('Edit Request Attendance')) {
            if (($attendance_request->created_by == Auth::user()->id || $attendance_request->employee_id == Auth::user()->employee->id || Auth::user()->type != 'employee') && $attendance_request->is_approved != 1) {
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
                if ($request->file('myDocument')) {
                    $docs = $request->file('myDocument');
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
                    'is_approved'   => null,
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
        if (\Auth::user()->can('Delete Request Attendance')) {
            if (($attendance_request->created_by == Auth::user()->id || $attendance_request->employee_id == Auth::user()?->employee?->id || Auth::user()->type != 'employee') && $attendance_request->is_approved != 1) {

                if ($attendance_request->docs) {
                    $filepath_array = explode('/', $attendance_request->docs);
                    $filename = array_pop($filepath_array);

                    // Check if the file exists before attempting to delete
                    if (Storage::disk('public')->exists("uploads/attendance_requests/$filename")) {
                        Storage::disk('public')->delete("uploads/attendance_requests/$filename");
                    }
                }

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
        $employee               = Employee::find($attendance_request->employee_id);
        $shiftTimes             = $attendance_request->shift_id ? ShiftTime::where('shift_type_id', $attendance_request->shift_id)->where('days', date('l', strtotime($attendance_request->date)))->select('start_time', 'end_time')->first() : null;

        if ($shiftTimes) {
            $shiftTimes         = !empty($shiftTimes->start_time) || !empty($shiftTimes->end_time) ? substr($shiftTimes->start_time, 0, 5) . ' - ' . substr($shiftTimes->end_time, 0, 5) : __('Holidays');
        }
        // $leavetype = LeaveType::find($leave->leave_type_id);

        return view('attendancerequest.action', compact('employee', 'attendance_request', 'shiftTimes'));
    }

    public function changeaction(Request $request)
    {
        $presentAttendance = AttendanceStatus::where('id', 1)->first();
        $attendance_request = AttendanceRequest::find($request->attendance_request_id);
        $date = $attendance_request->date;

        $start_time_cal = strtotime($attendance_request->start_time);
        $end_time_cal   = strtotime($attendance_request->end_time);

        if ($start_time_cal > $end_time_cal) {
            $end_time_cal += 86400;
        }

        $form = null;
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

        $form_attendance = null;

        $settings = Utility::settings();

        if ($form['is_approved']) {
            //* Method Create Attendance
            $shift_times = ShiftTime::where('shift_type_id', $attendance_request->employee->shift_type->id)
                ->where('days', date('l', strtotime($attendance_request->date)))
                ->first();

            //work hours
            $totalWorkHoursSeconds    = $end_time_cal - $start_time_cal;
            $hours                    = floor($totalWorkHoursSeconds / 3600);
            $mins                     = floor($totalWorkHoursSeconds / 60 % 60);
            $secs                     = floor($totalWorkHoursSeconds % 60);
            $workHours                = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            if ($shift_times->is_working) {
                $shift_startTime = strtotime($shift_times->start_time);
                $shift_endTime   = strtotime($shift_times->end_time);

                if ($shift_startTime > $shift_endTime) {
                    $shift_endTime += 86400;
                }

                // late
                if ($start_time_cal > ($shift_startTime + ((int)$settings['late_tolerance'] * 60))) {
                    $totalLateSeconds = $start_time_cal - ($shift_startTime + ((int)$settings['late_tolerance'] * 60));
                    $hours = floor($totalLateSeconds / 3600);
                    $mins  = floor($totalLateSeconds / 60 % 60);
                    $secs  = floor($totalLateSeconds % 60);
                    $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                } else {
                    $late  = '00:00:00';
                }

                //early Leaving
                if ($shift_endTime > $end_time_cal) {
                    $totalEarlyLeavingSeconds = $shift_endTime - $end_time_cal;
                    $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                    $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                    $secs                     = floor($totalEarlyLeavingSeconds % 60);
                    $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                } else {
                    $earlyLeaving             = '00:00:00';
                }

                if ($end_time_cal - $start_time_cal > 32400) {
                    //Overtime
                    $totalOvertimeSeconds = $end_time_cal - $start_time_cal - 32400;
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
                    'shift_type_id'         => $attendance_request->employee->shift_type_id,
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
                    'work_hours'            => $workHours,
                    'overtime'              => $workHours,
                    'total_rest'            => '00:00:00',
                    'created_by'            => $attendance_request->employee->user_id,
                    'attendance_type_id'    => 1, //* ON SITE
                    'coord_in'              => null,
                    'coord_out'             => null,
                    'is_valid'              => true,
                    'validate_by'           => Auth::user()->id,
                    'shift_type_id'         => $attendance_request->employee->shift_type_id,
                ];
            }
        }

        DB::transaction(function () use ($attendance_request, $form, $form_attendance) {
            AttendanceRequest::where('id', $attendance_request->id)->update($form);


            if ($form_attendance && $form['is_approved']) {
                AttendanceEmployee::where('employee_id', $form_attendance['employee_id'])->where('date', $form_attendance['date'])->delete();
                AttendanceEmployee::create($form_attendance);
            }
        });

        return redirect()->route('attendancerequest.index')->with('success', __('Request Attendance Successfully Approved / Rejeted'));
    }

    public function export(Request $request)
    {
        $name = 'Leave' . date('Y-m-d i:h:s');
        $data = Excel::download(new LeaveExport(), $name . '.xlsx');

        return $data;
    }

    public function getShift(Request $request)
    {
        $employee   = Employee::find($request->employee_id);
        $shifts     = ShiftType::where('branch_id', $employee->branch_id)->orderby('name', 'asc')->get()->pluck('name', 'id')->toArray();
        
        foreach ($shifts as $key => $shift) {
            $times = ShiftTime::where('shift_type_id', $key)->where('days', date('l', strtotime($request->date ? $request->date : date('Y-m-d'))))->select('start_time', 'end_time')->first();
            $formated_times = !empty($times->start_time) || !empty($times->end_time) ? substr($times->start_time, 0, 5) . ' - ' . substr($times->end_time, 0, 5) : __('Holidays');
            $shifts[$key] = $formated_times.  ' | ' . $shift;
        }

        return response()->json($shifts);
    }
}
