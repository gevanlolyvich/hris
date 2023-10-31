<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEmployee;
use App\Models\AttendanceStatus;
use App\Models\Employee;
use App\Models\Permit;
use App\Models\PermitType;
use App\Models\ShiftTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PermitController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('Manage Leave')) {
            $permits = Permit::where('created_by', '=', Auth::user()->creatorId())->get();
            if (Auth::user()->type == 'employee') {
                $user     = Auth::user();
                $employee = Employee::where('user_id', '=', $user->id)->first();
                $permits   = Permit::where('employee_id', '=', $employee->id)->get();
            } else {
                $permits = Permit::where('created_by', '=', Auth::user()->creatorId())->get();
            }

            return view('permit.index', compact('permits'));
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
            $permittypes   = PermitType::get();

            return view('permit.create', compact('employees', 'permittypes'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('Create Leave')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'permit_type_id'    => 'required',
                    'start_date'        => 'required',
                    'end_date'          => 'required',
                    'reason'            => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $employee = Employee::where('user_id', '=', Auth::user()->id)->first();
            $startDate = new \DateTime($request->start_date);
            $endDate = new \DateTime($request->end_date);
            $total_permit_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;
            $permit_type = PermitType::find($request->permit_type_id);

            $permit = new Permit();

            if (Auth::user()->type == "employee") {
                $permit->employee_id = $employee->id;
            } else {
                $permit->employee_id = $request->employee_id;
            }

            $employee = Employee::find($permit->employee_id);
            $document_path = null;
            if ($request->file('document')) {
                $docs = $request->file('document');
                $docName = time() . "_" . date('Y-m-d') . "_" . preg_replace('/\s+/', '', $employee->name) . "." . $docs->getClientOriginalExtension();
                $path = $docs->storeAs('uploads/permits', $docName, 'public');
                $document_path = env('APP_URL') . '/storage/' . $path;
            }

            $permit->permit_type_id     = $permit_type->id;
            $permit->start_date         = $request->start_date;
            $permit->end_date           = $request->end_date;
            $permit->total_permit_days  = $total_permit_days + 1;
            $permit->reason             = $request->reason;
            $permit->docs               = $document_path;
            $permit->status             = 'Pending';
            $permit->created_by         = Auth::user()->creatorId();

            $permit->save();
            return redirect()->route('permit.index')->with('success', __('Attendance Permit Successfully Created'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Permit $permit)
    {
        return redirect()->route('permit.index');
    }

    public function edit($id)
    {
        $permit = Permit::find($id);

        if (Auth::user()->can('Edit Leave')) {
            if ($permit->created_by == Auth::user()->creatorId()) {
                $employees  = Employee::get()->pluck('name', 'id');
                $permittype = PermitType::get()->pluck('name', 'id');

                return view('permit.edit', compact('permit', 'employees', 'permittype'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, $permit_id)
    {
        $permit = Permit::find($permit_id);
        if (Auth::user()->can('Edit Leave')) {
            if ($permit->created_by == Auth::user()->creatorId()) {
                $validator = Validator::make(
                    $request->all(),
                    [
                        'permit_type_id'    => 'required',
                        'start_date'        => 'required',
                        'end_date'          => 'required',
                        'reason'            => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }
                // return $request;

                //* Custom Form
                $startDate = new \DateTime($request->start_date);
                $endDate = new \DateTime($request->end_date);
                $total_permit_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;

                $date = date_create($request->date);
                $document_path = null;
                if ($request->file('document')) {
                    $docs = $request->file('document');
                    $docName = time() . "_" . date_format($date, "Y-m-d") . "_" . preg_replace('/\s+/', '', $permit->employee->name) . "." . $docs->getClientOriginalExtension();
                    $path = $docs->storeAs('uploads/permits', $docName, 'public');
                    $document_path = env('APP_URL') . '/storage/' . $path;
                }

                //* Input Data
                $form = [
                    'employee_id'   => $request->employee_id,
                    'start_date'    => $request->start_date,
                    'end_date'      => $request->end_date,
                    'total_permit_days' => $total_permit_days + 1,
                    'reason'        => $request->reason,
                    'docs'          => $document_path ? $document_path : $permit->docs,
                ];

                //* Update Data
                Permit::where('id', $permit->id)->update($form);
                return redirect()->route('permit.index')->with('success', __('Attendance Permit Successfully Updated'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Permit $permit)
    {
        if (Auth::user()->can('Delete Leave')) {
            if ($permit->created_by == Auth::user()->creatorId()) {
                $permit->delete();
                return redirect()->route('permit.index')->with('success', __('Attendance Permit Successfully Deleted'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function action($id)
    {
        $permit         = Permit::find($id);
        $employee       = Employee::find($permit->employee_id);

        return view('permit.action', compact('employee', 'permit'));
    }

    public function changeaction(Request $request)
    {
        $permit = Permit::find($request->permit_id);
        // return $permit;

        // if ($request->status == 'Approved') {
        //     $form = [
        //         'is_approved'   => true,
        //         'approved_by'   => Auth::user()->id
        //     ];
        // } elseif ($request->status == 'Reject') {
        //     $form = [
        //         'is_approved'   => false,
        //         'approved_by'   => Auth::user()->id
        //     ];
        // }
        $form = [
            'status'        => $request->status,
            'approved_by'   => Auth::user()->id
        ];

        $form_attendance = [];
        // if ($form['is_approved']) {
        //     $permitAttendance = AttendanceStatus::where('id', 3)->first();
        //     //* Check availability attendance
        //     $attendance = AttendanceEmployee::where('employee_id', $attendance_request->employee->id)->where('date', $date)->first();
        //     if ($attendance) {
        //         return redirect()->back()->with('error', __('You were present on that date already'));
        //     }

        //     //* Method Create Attendance
        //     $shift_times = ShiftTime::where('shift_type_id', $attendance_request->employee->shift_type->id)
        //         ->where('days', date('l'))
        //         ->first();


        //     if ($shift_times->is_working) {
        //         $startTime = $shift_times->start_time;
        //         $endTime = $shift_times->end_time;

        //         $totalLateSeconds = strtotime($date . $attendance_request->end_time) - strtotime($date . $startTime);

        //         $hours = floor($totalLateSeconds / 3600);
        //         $mins  = floor($totalLateSeconds / 60 % 60);
        //         $secs  = floor($totalLateSeconds % 60);
        //         $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

        //         //work hours
        //         $totalWorkHoursSeconds    = strtotime($date . $attendance_request->end_time) - strtotime($date . $attendance_request->start_time);
        //         $hours                    = floor($totalWorkHoursSeconds / 3600);
        //         $mins                     = floor($totalWorkHoursSeconds / 60 % 60);
        //         $secs                     = floor($totalWorkHoursSeconds % 60);
        //         $workHours                = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

        //         //early Leaving
        //         $totalEarlyLeavingSeconds = strtotime($date . $endTime) - strtotime($date . $attendance_request->end_time);
        //         $hours                    = floor($totalEarlyLeavingSeconds / 3600);
        //         $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
        //         $secs                     = floor($totalEarlyLeavingSeconds % 60);
        //         $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);


        //         if (strtotime($date . $attendance_request->end_time) > strtotime($date . $endTime)) {
        //             //Overtime
        //             $totalOvertimeSeconds = strtotime($date . $attendance_request->end_time) - strtotime($date . $endTime);
        //             $hours                = floor($totalOvertimeSeconds / 3600);
        //             $mins                 = floor($totalOvertimeSeconds / 60 % 60);
        //             $secs                 = floor($totalOvertimeSeconds % 60);
        //             $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
        //         } else {
        //             $overtime = '00:00:00';
        //         }

        //         $employee = $attendance_request->employee;
        //         $form_attendance = [
        //             'employee_id'           => $employee->id,
        //             'date'                  => $date,
        //             'attendance_status_id'  => $permitAttendance->id,
        //             'status'                => $permitAttendance->name,
        //             'clock_in'              => $attendance_request->start_time . ':00',
        //             'clock_out'             => $attendance_request->end_time . ':00',
        //             'late'                  => $late,
        //             'early_leaving'         => $earlyLeaving,
        //             'work_hours'            => $workHours,
        //             'overtime'              => $overtime,
        //             'total_rest'            => '00:00:00',
        //             'created_by'            => $employee->user_id,
        //             'attendance_type_id'    => 1, //* ON SITE
        //             'coord_in'              => null,
        //             'coord_out'             => null,
        //             'is_valid'              => true,
        //             'validate_by'           => Auth::user()->id,
        //         ];
        //     } else {
        //         $form_attendance = [
        //             'employee_id'           => $attendance_request->employee->id,
        //             'date'                  => $date,
        //             'attendance_status_id'  => $permitAttendance->id,
        //             'status'                => $permitAttendance->name,
        //             'clock_in'              => $attendance_request->start_time . ':00',
        //             'clock_out'             => $attendance_request->end_time . ':00',
        //             'late'                  => '00:00:00',
        //             'early_leaving'         => '00:00:00',
        //             'work_hours'            => '00:00:00',
        //             'overtime'              => '00:00:00',
        //             'total_rest'            => '00:00:00',
        //             'created_by'            => $attendance_request->employee->user_id,
        //             'attendance_type_id'    => 1, //* ON SITE
        //             'coord_in'              => null,
        //             'coord_out'             => null,
        //             'is_valid'              => true,
        //             'validate_by'           => Auth::user()->id,
        //         ];
        //     }
        // }

        DB::transaction(function () use ($permit, $form, $form_attendance) {
            Permit::where('id', $permit->id)->update($form);
            // AttendanceEmployee::create($form_attendance);
        });

        return redirect()->route('permit.index')->with('success', __('Request Attendance Successfully Updated'));
    }
}
