<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceMultipleExport;
use App\Models\AttendanceEmployee;
use App\Models\AttendanceStatus;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\IpRestrict;
use App\Models\ShiftTime;
use App\Models\User;
use App\Models\Utility;
use App\Models\LogAttendance;
use App\Utilities\DistanceCalculator;
use App\Exports\NotClockInExport;
use App\Models\ShiftHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceEmployeeController extends Controller
{
    public function index(Request $request)
    {
        if (\Auth::user()->can('Manage Attendance')) {
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

            $branch = $branch_id?->isNotEmpty() ? Branch::whereIn('id', $branch_id)->get()->pluck('name', 'id') : Branch::get()->pluck('name', 'id');

            $department = $branch_id?->isNotEmpty() ? Department::whereIn('branch_id', $branch_id)->get()->pluck('name', 'id') : Department::get()->pluck('name', 'id');

            $is_valid = $request->query('is_valid', null);

            if (\Auth::user()->type == 'employee') {

                $emp = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;

                $userId = \Auth::user()->employee->user_id;
                $subordinates = \Auth::user()->employee->subordinatesFlatten();

                // Check if employee managing other employee or not
                if ($subordinates->isNotEmpty()) {
                    $employees = collect();
                    foreach ($subordinates as $subordinate) {
                        $employees->push($subordinate->id);
                    }

                    $employees->push($emp);

                    $attendanceEmployee = AttendanceEmployee::whereIn('employee_id', $employees);
                } else {
                    $attendanceEmployee = AttendanceEmployee::where('employee_id', $emp);
                }

                if ($request->type == 'monthly' && !empty($request->month)) {
                    $month = date('m', strtotime($request->month));
                    $year  = date('Y', strtotime($request->month));

                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                    // old date
                    // $end_date   = date($year . '-' . $month . '-t');

                    $attendanceEmployee->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                } elseif ($request->type == 'daily' && !empty($request->date)) {
                    $attendanceEmployee->where('date', $request->date);
                } else {
                    $attendanceEmployee->where('date', date('Y-m-d'));
                }

                if ($is_valid != null && $is_valid == '0') {
                    $attendanceEmployee->whereNull('is_valid');
                } else if ($is_valid != null && $is_valid == '1') {
                    $attendanceEmployee->where('is_valid', 1);
                }

                $attendanceEmployee = $attendanceEmployee->orderBy('date', 'desc')->withAggregate('employee', 'name')->orderBy('employee_name', 'asc')->get();
            } else {
                $employee = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->select('id') : Employee::select('id');
                if (!empty($request->branch)) {
                    $employee->where('branch_id', $request->branch);
                }

                if (!empty($request->department)) {
                    $employee->where('department_id', $request->department);
                }

                if (empty($request->department) && empty($request->branch)) {
                    $department = [];
                }

                $employee = $employee?->orderby('name', 'asc')?->get()?->pluck('id');

                $attendanceEmployee = AttendanceEmployee::whereIn('employee_id', $employee);

                if ($request->type == 'monthly' && !empty($request->month)) {
                    $month = date('m', strtotime($request->month));
                    $year  = date('Y', strtotime($request->month));

                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                    $attendanceEmployee->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                } else if ($request->type == 'daily' && !empty($request->date)) {
                    $attendanceEmployee->where('date', $request->date);
                } else {
                    $attendanceEmployee->where('date', date('Y-m-d'));
                }

                if ($is_valid != null && $is_valid == '0') {
                    $attendanceEmployee->whereNull('is_valid');
                } else if ($is_valid != null && $is_valid == '1') {
                    $attendanceEmployee->where('is_valid', true);
                }

                $attendanceEmployee = $attendanceEmployee->orderBy('date', 'desc')->withAggregate('employee', 'name')->orderBy('employee_name', 'asc')->get();
            }

            $branchCoordinates = Branch::select('name', 'latitude', 'longitude', 'tolerance')->get();

            foreach($attendanceEmployee as $attendance) {
                if ($attendance->coord_in || $attendance->coord_out) {
                    $nearest_in         = null;
                    $nearest_in_coord   = null;
                    $near_in_name       = null;
                    $near_in_radius     = null;
                    $nearest_out        = null;
                    $nearest_out_coord  = null;
                    $near_out_name      = null;
                    $near_out_radius    = null;
    
                    $attendance_in      = explode(', ', $attendance->coord_in);
                    $attendance_out     = explode(', ', $attendance->coord_out);

                    foreach ($branchCoordinates as $coordinate) {
                        if (sizeof($attendance_in) > 1) {
                            $distance_in       = DistanceCalculator::haversineDistance($attendance_in[0], $attendance_in[1], (float)$coordinate['latitude'], (float)$coordinate['longitude']);

                            if ($nearest_in > $distance_in || $nearest_in == null) {
                                $nearest_in         = $distance_in;
                                $nearest_in_coord   = $coordinate->latitude . ', ' . $coordinate->longitude;
                                $near_in_name       = $coordinate->name;
                                $near_in_radius     = $coordinate->tolerance;
                            }
                        }

                        if (sizeof($attendance_out) > 1) {
                            $distance_out       = DistanceCalculator::haversineDistance($attendance_out[0], $attendance_out[1], (float)$coordinate['latitude'], (float)$coordinate['longitude']);

                            if ($nearest_out > $distance_out || $nearest_out == null) {
                                $nearest_out        = $distance_out;
                                $nearest_out_coord  = $coordinate->latitude . ', ' . $coordinate->longitude;
                                $near_out_name      = $coordinate->name;
                                $near_out_radius    = $coordinate->tolerance;
                            }
                        }
                    }

                    $attendance['location_in_coordinate']   = $nearest_in_coord;
                    $attendance['location_in_address']      = $near_in_name;
                    $attendance['location_in_radius']       = $near_in_radius;
                    $attendance['location_out_coordinate']  = $nearest_out_coord;
                    $attendance['location_out_address']     = $near_out_name;
                    $attendance['location_out_radius']      = $near_out_radius;
                }

            }

            $emp = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;

            $branch_count = 2;
            foreach ($branch as $index => $b) {
                if ($b == 'Head Office') {
                    $branch[$index] = '1. '.  $b;
                } else {
                    $branch[$index] = $branch_count. '. ' . __($b);
                    $branch_count += 1;
                }
            }

            return view('attendance.index', compact('attendanceEmployee', 'branch', 'department', 'emp'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Attendance')) {
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

            $employees = $branch_id?->isNotEmpty() ? User::where('is_active', 1)->where('type', '=', "employee")->whereIn('branch_id', $branch_id)->orderby('name', 'asc')->get()->pluck('name', 'id') : User::where('is_active', 1)->where('type', '=', "employee")->orderby('name', 'asc')->get()->pluck('name', 'id');

            return view('attendance.create', compact('employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Attendance')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'date' => 'required',
                    'clock_in' => 'required',
                    'clock_out' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $startTime  = Utility::getValByName('company_start_time');
            $endTime    = Utility::getValByName('company_end_time');
            $attendance = AttendanceEmployee::where('employee_id', '=', $request->employee_id)->where('date', '=', $request->date)->where('clock_out', '=', '00:00:00')->get()->toArray();
            if ($attendance) {
                return redirect()->back()->with('error', __('Employee Attendance Already Created.'));
            } else {
                $date = date("Y-m-d");

                $totalLateSeconds = strtotime($request->clock_in) - strtotime($date . $startTime);

                $hours = floor($totalLateSeconds / 3600);
                $mins  = floor($totalLateSeconds / 60 % 60);
                $secs  = floor($totalLateSeconds % 60);
                $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                //early Leaving
                $totalEarlyLeavingSeconds = strtotime($date . $endTime) - strtotime($request->clock_out);
                $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                $secs                     = floor($totalEarlyLeavingSeconds % 60);
                $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                // get attendance status persent for attendance_status model, when employee clock in
                $status = AttendanceStatus::where('id', 1)->first();


                if (strtotime($request->clock_out) > strtotime($date . $endTime)) {
                    //Overtime
                    $totalOvertimeSeconds = strtotime($request->clock_out) - strtotime($date . $endTime);
                    $hours                = floor($totalOvertimeSeconds / 3600);
                    $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                    $secs                 = floor($totalOvertimeSeconds % 60);
                    $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                } else {
                    $overtime = '00:00:00';
                }

                // calculate work hours
                $totalWorkSeconds = strtotime($request->clock_in . ':00') - strtotime($request->clock_in . ':00');
                $hours                = floor($totalWorkSeconds / 3600);
                $mins                 = floor($totalWorkSeconds / 60 % 60);
                $secs                 = floor($totalWorkSeconds % 60);
                $workhours             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                $employeeAttendance                         = new AttendanceEmployee();
                $employeeAttendance->employee_id            = $request->employee_id;
                $employeeAttendance->date                   = $request->date;
                $employeeAttendance->attendance_status_id   = $status->id;
                $employeeAttendance->status                 = $status->name;
                $employeeAttendance->clock_in               = $request->clock_in . ':00';
                $employeeAttendance->clock_out              = $request->clock_out . ':00';
                $employeeAttendance->late                   = $late;
                $employeeAttendance->early_leaving          = $earlyLeaving;
                $employeeAttendance->overtime               = $overtime;
                $employeeAttendance->total_rest             = '00:00:00';
                $employeeAttendance->work_hours             = $workhours;
                $employeeAttendance->created_by             = \Auth::user()->id;

                $employeeAttendance->save();

                return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully created.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function show(Request $request)
    {
        return redirect()->route('attendanceemployee.index');
    }

    public function edit($id)
    {
        if (\Auth::user()->can('Edit Attendance')) {
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

            $attendanceEmployee = AttendanceEmployee::where('id', $id)->first();
            $employees          = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id') : Employee::where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id');

            return view('attendance.edit', compact('attendanceEmployee', 'employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, $id)
    {
        // return $request;
        // Retrieve the latitude and longitude from the request

        $picture_path = null;
        $employee = Employee::where('user_id', Auth::user()->id)->first();

        $latitude   = $request->input('latitude');
        $longitude  = $request->input('longitude');
        $accuracy   = $request->input('accuracy');

        // if ($latitude == '0' && $longitude == '0' && $accuracy == '0') {
        //     return redirect()->back()->with('error', __('Invalid GPS Data'));
        // }

        $coord_in = "$latitude, $longitude, $accuracy";
        $coord_out = "$latitude, $longitude, $accuracy";

        // process image file
        if ($request->input('picture_out')) {
            $base64ImageData = $request->input('picture_out');
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64ImageData));
            $pictureName = 'attendance_' . time() . '_' . date('Y-m-d') . '_' . preg_replace('/\s+/', '', $employee->name) . '.png';
            Storage::disk('public')->put('uploads/attendance/' . $pictureName, $imageData);
            $picture_path = env('APP_URL') . '/storage/uploads/attendance/' . $pictureName;
        } else {
            return redirect()->back()->with('error', __('The picture field is required.'));
        }

        $employeeId      = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
        $todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)
            ->where('date', date('Y-m-d'))
            ->where('shift_type_id', $request->shift_type_id)
            ->first();

        $shift_times = ShiftTime::where('shift_type_id', $todayAttendance?->shift_type_id)
            ->where('days', date('l'))
            ->first();
        $date           = date("Y-m-d");
        $yesterday_date = date("Y-m-d", strtotime('yesterday'));
        $tomorrow_date  = date("Y-m-d", strtotime('tomorrow'));
        $time           = date("H:i:s");
        $timestamp      = time();

        $settings = Utility::settings();

        // yesterday shift and attendance for cross day attendance operation
        $yesterdayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)->where('date', date('Y-m-d', strtotime('yesterday')))->first();
        $yesterday_shift_times = ShiftTime::where('shift_type_id', $yesterdayAttendance?->shift_type_id)
            ->where('days', date('l', strtotime('yesterday')))
            ->first();

        // tomorrow shift
        $tomorrow_shift_times = ShiftTime::where('shift_type_id', \Auth::user()->employee->shift_type->id)
            ->where('days', date('l', strtotime('tomorrow')))
            ->first();

        // calculate default clock out for cross day shift
        $today_clock_in_second       = strtotime($shift_times?->start_time) - strtotime($date) - 3600;
        $today_hours                  = floor($today_clock_in_second / 3600);
        $today_mins                   = floor($today_clock_in_second / 60 % 60);
        $today_secs                   = floor($today_clock_in_second % 60);
        // $default_clock_out            = sprintf('%02d:%02d:%02d', $today_hours, $today_mins, $today_secs);

        // calculate default clock out for cross day shift
        $clockoutSeconds              = strtotime($yesterday_shift_times?->end_time) - strtotime($date);
        $hours                        = floor($clockoutSeconds / 3600);
        $mins                         = floor($clockoutSeconds / 60 % 60);
        $secs                         = floor($clockoutSeconds % 60);
        // $default_clock_out_cross_day  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

        // calculate absolute out time
        $today_absolute_in            = strtotime($date . " " . sprintf('%02d:%02d:%02d', $today_hours, $today_mins, $today_secs));
        $yesterday_absolute_out_time  = sprintf('%02d:%02d:%02d', $hours + 1, $mins, $secs);
        // $yesterday_absolute_out       = strtotime("$date $yesterday_absolute_out_time");

        // calculate abolute in for tommorow
        $tomorrow_clock_in_second     = strtotime($tomorrow_shift_times->start_time) - strtotime($date) - 3600;
        $tomorrow_hours               = floor($tomorrow_clock_in_second / 3600);
        $tomorrow_mins                = floor($tomorrow_clock_in_second / 60 % 60);
        $tomorrow_secs                = floor($tomorrow_clock_in_second % 60);
        $tomorrow_absolute_in         = strtotime($tomorrow_date . " " . sprintf('%02d:%02d:%02d', $tomorrow_hours, $tomorrow_mins, $tomorrow_secs));

        $today_cross_day = $shift_times?->start_time > $shift_times?->end_time ? true : false;
        $yesterday_cross_day = $yesterday_shift_times?->start_time > $yesterday_shift_times?->end_time ? true : false;

        if ($yesterdayAttendance && !$todayAttendance && ($timestamp <= $today_absolute_in || $yesterday_cross_day || !$today_absolute_in)) {
            if ($yesterday_shift_times?->is_working) {
                $startTime = $yesterday_shift_times?->start_time;
                $endTime = $yesterday_shift_times?->end_time;

                if (Auth::user()->type == 'employee') {
                    //early Leaving
                    if (time() < strtotime($date . $endTime)) {
                        $totalEarlyLeavingSeconds = strtotime($date . $endTime) - time();
                        $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                        $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                        $secs                     = floor($totalEarlyLeavingSeconds % 60);
                        $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                    } else {
                        $earlyLeaving             = '00:00:00';
                    }

                    //Overtime
                    if (time() > strtotime($date . $endTime)) {
                        $totalOvertimeSeconds = time() - strtotime($date . $endTime);
                        $hours                = floor($totalOvertimeSeconds / 3600);
                        $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                        $secs                 = floor($totalOvertimeSeconds % 60);
                        $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                    } else {
                        $overtime               = '00:00:00';
                    }

                    $attendanceEmployee                = AttendanceEmployee::find($id);
                    $attendanceEmployee->clock_out     = $time;
                    $attendanceEmployee->early_leaving = $earlyLeaving;
                    $attendanceEmployee->overtime      = $overtime;
                    $attendanceEmployee->coord_out     = $coord_out;

                    // calculate work hours
                    $totalWorkSeconds     = time() - strtotime($yesterday_date . $attendanceEmployee->clock_in);
                    $hours                = floor($totalWorkSeconds / 3600);
                    $mins                 = floor($totalWorkSeconds / 60 % 60);
                    $secs                 = floor($totalWorkSeconds % 60);
                    $workhours            = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                    $attendanceEmployee->work_hours    = $workhours;
                    $attendanceEmployee->picture_out   = $picture_path;
                    $attendanceEmployee->source_out    = 'Application';

                    $attendanceEmployee->save();

                    $logForm =  [
                        'personel_id'   => $employee->personel_id,
                        'date'          => $attendanceEmployee->date,
                        'coordinate'    => $coord_out,
                        'min'           => $attendanceEmployee->clock_in,
                        'max'           => $time,
                        'max_source'    => 'Application',
                        'shift_id'      => $attendanceEmployee->shift_type_id,
                    ];

                    LogAttendance::create($logForm);

                    return redirect()->route('attendanceemployee.index', ['type' => 'daily', 'date' => $attendanceEmployee->date])->with([
                        'success' => __('Employee successfully Clock Out.'),
                        'employee' => $employee,
                    ]);
                } else {
                    //late
                    $totalLateSeconds = strtotime($request->clock_in) - strtotime($date . $startTime) + ((int)$settings['late_tolerance'] * 60);

                    $hours = floor($totalLateSeconds / 3600);
                    $mins  = floor($totalLateSeconds / 60 % 60);
                    $secs  = floor($totalLateSeconds % 60);
                    $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                    //early Leaving
                    if (strtotime($request->clock_out) < strtotime($date . $endTime)) {
                        $totalEarlyLeavingSeconds = strtotime($date . $endTime) - strtotime($request->clock_out);
                        $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                        $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                        $secs                     = floor($totalEarlyLeavingSeconds % 60);
                        $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                    } else {
                        $earlyLeaving             = '00:00:00';
                    }

                    if (strtotime($request->clock_out) > strtotime($date . $endTime)) {
                        //Overtime
                        $totalOvertimeSeconds = strtotime($request->clock_out) - strtotime($date . $endTime);
                        $hours                = floor($totalOvertimeSeconds / 3600);
                        $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                        $secs                 = floor($totalOvertimeSeconds % 60);
                        $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                    } else {
                        $overtime = '00:00:00';
                    }

                    $attendanceEmployee                = AttendanceEmployee::find($id);
                    $attendanceEmployee->employee_id   = $request->employee_id;
                    $attendanceEmployee->date          = $request->date;
                    $attendanceEmployee->clock_in      = $request->clock_in;
                    $attendanceEmployee->clock_out     = $request->clock_out;
                    $attendanceEmployee->late          = $late;
                    $attendanceEmployee->early_leaving = $earlyLeaving;
                    $attendanceEmployee->overtime      = $overtime;
                    $attendanceEmployee->total_rest    = '00:00:00';
                    $attendanceEmployee->coord_out     = $coord_out;
                    $attendanceEmployee->picture_out   = $picture_path;
                    $attendanceEmployee->source_out    = 'Application';

                    $attendanceEmployee->save();

                    return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully updated.'));
                }
            } else {
                $startTime = Utility::getValByName('company_start_time');
                $endTime   = Utility::getValByName('company_end_time');
                if (Auth::user()->type == 'employee') {
                    $attendanceEmployee                = AttendanceEmployee::find($id);
                    $attendanceEmployee->clock_out     = $time;
                    $attendanceEmployee->early_leaving = '00:00:00';
                    $attendanceEmployee->overtime      = '00:00:00';
                    $attendanceEmployee->late          = '00:00:00';
                    $attendanceEmployee->total_rest    = '00:00:00';
                    $attendanceEmployee->work_hours    = '00:00:00';
                    $attendanceEmployee->coord_out     = $coord_out;
                    $attendanceEmployee->picture_out   = $picture_path;
                    $attendanceEmployee->source_out    = 'Application';
                    $attendanceEmployee->save();

                    $logForm =  [
                        'personel_id'   => $employee->personel_id,
                        'date'          => $attendanceEmployee->date,
                        'coordinate'    => $coord_out,
                        'min'           => $attendanceEmployee->clock_in,
                        'max'           => $time,
                        'max_source'    => 'Application',
                        'shift_id'      => $attendanceEmployee->shift_type_id,
                    ];

                    LogAttendance::create($logForm);

                    return redirect()->route('attendanceemployee.index', ['type' => 'daily', 'date' => $attendanceEmployee->date])->with([
                        'success' => __('Employee successfully Clock Out.'),
                        'employee' => $employee,
                    ]);
                } else {
                    $attendanceEmployee                = AttendanceEmployee::find($id);
                    $attendanceEmployee->employee_id   = $request->employee_id;
                    $attendanceEmployee->date          = $request->date;
                    $attendanceEmployee->clock_in      = $request->clock_in;
                    $attendanceEmployee->clock_out     = $request->clock_out;
                    $attendanceEmployee->late          = '00:00:00';
                    $attendanceEmployee->early_leaving = '00:00:00';
                    $attendanceEmployee->overtime      = '00:00:00';
                    $attendanceEmployee->total_rest    = '00:00:00';
                    $attendanceEmployee->work_hours    = '00:00:00';
                    $attendanceEmployee->coord_out     = $coord_out;
                    $attendanceEmployee->picture_out   = $picture_path;
                    $attendanceEmployee->source_out    = 'Application';

                    $attendanceEmployee->save();

                    return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully updated.'));
                }
            }
            // } elseif ($todayAttendance && ($timestamp <= $tomorrow_absolute_in || $today_cross_day || !$tomorrow_absolute_in)) {
        } elseif ($todayAttendance || ($timestamp <= $tomorrow_absolute_in || $today_cross_day || !$tomorrow_absolute_in)) {
            if ($shift_times?->is_working) {
                $startTime = $shift_times?->start_time;
                $endTime = $shift_times?->end_time;

                if (Auth::user()->type == 'employee') {
                    //early Leaving
                    if (!$today_cross_day && time() < strtotime($date . $endTime)) {
                        $totalEarlyLeavingSeconds = strtotime($date . $endTime) - time();
                        $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                        $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                        $secs                     = floor($totalEarlyLeavingSeconds % 60);
                        $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                    } elseif ($today_cross_day) {
                        $totalEarlyLeavingSeconds = strtotime($tomorrow_date . $endTime) - time();
                        $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                        $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                        $secs                     = floor($totalEarlyLeavingSeconds % 60);
                        $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                    } else {
                        $earlyLeaving             = '00:00:00';
                    }

                    //Overtime
                    if (!$today_cross_day && time() > strtotime($date . $endTime)) {
                        $totalOvertimeSeconds = time() - strtotime($date . $endTime);
                        $hours                = floor($totalOvertimeSeconds / 3600);
                        $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                        $secs                 = floor($totalOvertimeSeconds % 60);
                        $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                    } else {
                        $overtime = '00:00:00';
                    }

                    $attendanceEmployee                = AttendanceEmployee::find($id);
                    $attendanceEmployee->clock_out     = $time;
                    $attendanceEmployee->early_leaving = $earlyLeaving;
                    $attendanceEmployee->overtime      = $overtime;

                    // calculate work hours
                    $totalWorkSeconds = time() - strtotime($attendanceEmployee->clock_in);
                    $hours                = floor($totalWorkSeconds / 3600);
                    $mins                 = floor($totalWorkSeconds / 60 % 60);
                    $secs                 = floor($totalWorkSeconds % 60);
                    $workhours             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                    $attendanceEmployee->work_hours      = $workhours;
                    $attendanceEmployee->coord_out       = $coord_out;
                    $attendanceEmployee->picture_out     = $picture_path;
                    $attendanceEmployee->source_out      = 'Application';
                    $attendanceEmployee->save();

                    $logForm =  [
                        'personel_id'   => $employee->personel_id,
                        'date'          => $date,
                        'coordinate'    => $coord_out,
                        'min'           => $attendanceEmployee->clock_in,
                        'max'           => $time,
                        'max_source'    => 'Application',
                        'shift_id'      => $attendanceEmployee->shift_type_id,
                    ];

                    LogAttendance::create($logForm);

                    return redirect()->route('attendanceemployee.index')->with([
                        'success' => __('Employee successfully Clock Out.'),
                        'employee' => $employee,
                    ]);
                } else {
                    //late
                    $totalLateSeconds = strtotime($request->clock_in) - strtotime($date . $startTime);

                    $hours = floor($totalLateSeconds / 3600);
                    $mins  = floor($totalLateSeconds / 60 % 60);
                    $secs  = floor($totalLateSeconds % 60);
                    $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                    //early Leaving
                    if (strtotime($request->clock_out) < strtotime($date . $endTime)) {
                        $totalEarlyLeavingSeconds = strtotime($date . $endTime) - strtotime($request->clock_out);
                        $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                        $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                        $secs                     = floor($totalEarlyLeavingSeconds % 60);
                        $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                    } else {
                        $earlyLeaving             = '00:00:00';
                    }


                    //Overtime
                    if (strtotime($request->clock_out) > strtotime($date . $endTime)) {
                        $totalOvertimeSeconds = strtotime($request->clock_out) - strtotime($date . $endTime);
                        $hours                = floor($totalOvertimeSeconds / 3600);
                        $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                        $secs                 = floor($totalOvertimeSeconds % 60);
                        $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                    } else {
                        $overtime = '00:00:00';
                    }

                    $attendanceEmployee                = AttendanceEmployee::find($id);
                    $attendanceEmployee->employee_id   = $request->employee_id;
                    $attendanceEmployee->date          = $request->date;
                    $attendanceEmployee->clock_in      = $request->clock_in;
                    $attendanceEmployee->clock_out     = $request->clock_out;
                    $attendanceEmployee->late          = $late;
                    $attendanceEmployee->early_leaving = $earlyLeaving;
                    $attendanceEmployee->overtime      = $overtime;
                    $attendanceEmployee->total_rest    = '00:00:00';
                    $attendanceEmployee->coord_out     = $coord_out;
                    $attendanceEmployee->picture_out   = $picture_path;
                    $attendanceEmployee->source_out    = 'Application';

                    $attendanceEmployee->save();

                    return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully updated.'));
                }
            } else {
                $startTime = Utility::getValByName('company_start_time');
                $endTime   = Utility::getValByName('company_end_time');
                if (Auth::user()->type == 'employee') {
                    $attendanceEmployee                = AttendanceEmployee::find($id);
                    $attendanceEmployee->clock_out     = $time;
                    $attendanceEmployee->early_leaving = '00:00:00';
                    $attendanceEmployee->overtime      = '00:00:00';
                    $attendanceEmployee->late          = '00:00:00';
                    $attendanceEmployee->total_rest    = '00:00:00';
                    $attendanceEmployee->work_hours    = '00:00:00';
                    $attendanceEmployee->coord_out     = $coord_out;
                    $attendanceEmployee->picture_out   = $picture_path;
                    $attendanceEmployee->source_out    = 'Application';
                    $attendanceEmployee->save();

                    $logForm =  [
                        'personel_id'   => $employee->personel_id,
                        'date'          => $date,
                        'coordinate'    => $coord_out,
                        'min'           => $attendanceEmployee->clock_in,
                        'max'           => $time,
                        'max_source'    => 'Application',
                        'shift_id'      => $attendanceEmployee->shift_type_id,
                    ];

                    LogAttendance::create($logForm);

                    return redirect()->route('attendanceemployee.index')->with([
                        'success' => __('Employee successfully Clock Out.'),
                        'employee' => $employee,
                    ]);
                } else {
                    $attendanceEmployee                = AttendanceEmployee::find($id);
                    $attendanceEmployee->employee_id   = $request->employee_id;
                    $attendanceEmployee->date          = $request->date;
                    $attendanceEmployee->clock_in      = $request->clock_in;
                    $attendanceEmployee->clock_out     = $request->clock_out;
                    $attendanceEmployee->late          = '00:00:00';
                    $attendanceEmployee->early_leaving = '00:00:00';
                    $attendanceEmployee->overtime      = '00:00:00';
                    $attendanceEmployee->total_rest    = '00:00:00';
                    $attendanceEmployee->coord_out     = $coord_out;
                    $attendanceEmployee->picture_out   = $picture_path;
                    $attendanceEmployee->source_out    = 'Application';

                    $attendanceEmployee->save();

                    return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully updated.'));
                }
            }
        } elseif ((!$todayAttendance && !$yesterday_cross_day) || (!$yesterdayAttendance && $yesterday_cross_day)) {
            return redirect()->back()->with('error', __('Employee are not allow to clock out with out clock in first'));
        } else {
            return redirect()->back()->with('error', __('Clock Out Data Invalid'));
        }
    }

    public function destroy($id)
    {
        if (\Auth::user()->can('Delete Attendance')) {
            $attendance = AttendanceEmployee::where('id', $id)->first();

            $attendance->delete();

            return redirect()->back()->with('success', __('Attendance successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function attendance(Request $request)
    {
        $settings = Utility::settings();

        $validator = \Validator::make(
            $request->all(),
            [
                'attendance_type' => 'required',
                'latitude' => 'required',
                'longitude' => 'required',
                'accuracy' => 'required',
                'picture' => $settings['photo_on_clock'] == 'Required' ? 'required' : 'nullable',
                'shift_type_id' => 'required',
            ]   
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        // if ($request->latitude == '0' && $request->longitude == '0' && $request->accuracy == '0') {
        //     return redirect()->back()->with('error', __('Invalid GPS Data'));
        // }

        $picture_path = null;
        $employee = Employee::where('is_active', 1)->where('user_id', Auth::user()->id)->first();

        if (!empty($employee)) {
            if ($settings['ip_restrict'] == 'on') {
                $userIp = request()->ip();
                $ip     = IpRestrict::where('created_by', \Auth::user()->creatorId())->whereIn('ip', [$userIp])->first();
                if (!empty($ip)) {
                    return redirect()->back()->with('error', __('this ip is not allowed to clock in & clock out.'));
                }
            }

            // Retrieve the latitude and longitude from the request
            $latitude   = $request->input('latitude');
            $longitude  = $request->input('longitude');
            $accuracy   = $request->input('accuracy');
            $coord_in   = "$latitude, $longitude, $accuracy";
            $coord_out  = "$latitude, $longitude, $accuracy";

            // Retrieve the additional information
            $note               = $request->input('notes');
            $attendance_type    = $request->input('attendance_type');

            $is_valid_shift     = $employee->shift_type_id == $request->shift_type_id ? true : null;
            $is_valid_location  = null;

            if ($attendance_type == '1') {
                // check employee clock in location with branch to validate attendance

                $branch_data = Branch::where('id', \Auth::user()->employee->branch_id)->first();
                $distance = DistanceCalculator::haversineDistance($latitude, $longitude, (float)$branch_data['latitude'], (float)$branch_data['longitude']);

                $is_valid_location = ($accuracy + (float)$branch_data['tolerance']) >= $distance ? true : null;
            } else if ($attendance_type == '3' && !empty($employee->coordinate)) {
                $home_coordinate = explode(', ', $employee->coordinate);
                $home_latitude = $home_coordinate[0];
                $home_longitude = $home_coordinate[1];
                $home_tolerance = $home_coordinate[2];
                $distance = DistanceCalculator::haversineDistance($latitude, $longitude, (float)$home_latitude, (float)$home_longitude);

                $is_valid_location = ($accuracy + (float)$home_tolerance) >= $distance ? true : null;
            }

            $is_valid           = $is_valid_shift && $is_valid_location ? true : null;

            $date = date("Y-m-d");
            $time = date("H:i:s");

            $employeeId      = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
            $todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)
                ->where('date', date('Y-m-d'))
                ->where('shift_type_id', $request->shift_type_id)
                ->first();
            if ($todayAttendance) {
                return redirect()->back()->with('error', __('Today you have clocked in and clocked out.'));
            }

            $shift_times = ShiftTime::where('shift_type_id', $request->shift_type_id)
                ->where('days', date('l'))
                ->first();
            $cross_day = $shift_times->start_time > $shift_times->end_time ? true : false;

            // calculate default clock out for cross day shift
            $clockoutSeconds              = strtotime($shift_times->end_time) - strtotime($date) - 3600;
            $hours                        = floor($clockoutSeconds / 3600);
            $mins                         = floor($clockoutSeconds / 60 % 60);
            $secs                         = floor($clockoutSeconds % 60);
            $default_clock_out_cross_day  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            // process image file
            if ($request->input('picture')) {
                $base64ImageData = $request->input('picture');
                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64ImageData));
                $pictureName = 'attendance_' . time() . '_' . date('Y-m-d') . '_' . preg_replace('/\s+/', '', $employee?->name) . '.png';
                Storage::disk('public')->put('uploads/attendance/' . $pictureName, $imageData);
                $picture_path = env('APP_URL') . '/storage/uploads/attendance/' . $pictureName;
            }

            // Check clock in if today is shift in cross day mode
            if ($shift_times->is_working) {
                if (empty($todayAttendance)) {

                    // $startTime = Utility::getValByName('company_start_time');
                    // $endTime   = Utility::getValByName('company_end_time');
                    $startTime = $shift_times->start_time;
                    $endTime = $shift_times->end_time;


                    $attendance = AttendanceEmployee::orderBy('id', 'desc')->where('employee_id', '=', $employeeId)->where('clock_out', '=', '00:00:00')->first();

                    if ($attendance != null) {
                        $attendance            = AttendanceEmployee::find($attendance->id);
                        $attendance->clock_out = $time;
                        $attendance->coord_out = $coord_out;
                        $attendance->note      = $note;
                        $attendance->save();
                    }

                    //late
                    if (time() > (strtotime($date . $startTime) + ((int)$settings['late_tolerance'] * 60))) {
                        $totalLateSeconds = time() - (strtotime($date . $startTime) + ((int)$settings['late_tolerance'] * 60));
                        $hours            = floor($totalLateSeconds / 3600);
                        $mins             = floor($totalLateSeconds / 60 % 60);
                        $secs             = floor($totalLateSeconds % 60);
                        $late             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                    } else {
                        $late             = '00:00:00';
                    }

                    $presentStatus = AttendanceStatus::where('id', 1)->first();

                    $employeeAttendance                         = new AttendanceEmployee();
                    $employeeAttendance->employee_id            = $employeeId;
                    $employeeAttendance->date                   = $date;
                    $employeeAttendance->attendance_status_id   = $presentStatus->id;
                    $employeeAttendance->status                 = $presentStatus->name;
                    $employeeAttendance->clock_in               = $time;
                    $employeeAttendance->clock_out              = $time;
                    $employeeAttendance->late                   = $late;
                    $employeeAttendance->early_leaving          = '00:00:00';
                    $employeeAttendance->overtime               = '00:00:00';
                    $employeeAttendance->total_rest             = '00:00:00';
                    $employeeAttendance->work_hours             = '00:00:00';
                    $employeeAttendance->coord_in               = $coord_in;
                    $employeeAttendance->note                   = $note;
                    $employeeAttendance->is_valid               = $is_valid;
                    $employeeAttendance->validate_by            = $is_valid ? 1 : null;
                    $employeeAttendance->attendance_type_id     = $attendance_type;
                    $employeeAttendance->picture_in             = $picture_path;
                    $employeeAttendance->created_by             = \Auth::user()->id;
                    $employeeAttendance->shift_type_id          = $request->shift_type_id;
                    $employeeAttendance->source_in              = 'Application';
                    $employeeAttendance->save();

                    $logForm =  [
                        'personel_id'   => $employee->personel_id,
                        'date'          => $date,
                        'coordinate'    => $coord_in,
                        'min'           => $time,
                        'max'           => $time,
                        'min_source'    => 'Application',
                        'max_source'    => 'Application',
                        'shift_id'      => $request->shift_type_id,
                    ];

                    //* Shift History
                    $last_shift_history = ShiftHistory::where('employee_id', $employeeId)->orderBy('id', 'desc')->first();
                    if (empty($last_shift_history) || $last_shift_history->shift_type_id != $request->shift_type_id) {
                        ShiftHistory::create(['employee_id' => $employeeId, 'shift_type_id' => $request->shift_type_id]);
                    }
                    // return $last_shift_history;

                    LogAttendance::create($logForm);

                    return redirect()->route('attendanceemployee.index')->with([
                        'success' => __('Employee Successfully Clock In.'),
                        'employee' => $employee,
                    ]);
                } else {
                    return redirect()->back()->with('error', __('Employee are not allow multiple time clock in & clock for every day.'));
                }
            } else {
                $checkDb = AttendanceEmployee::where('employee_id', '=', \Auth::user()->id)->get()->toArray();
                if (empty($checkDb)) {
                    $employeeAttendance                         = new AttendanceEmployee();
                    $employeeAttendance->employee_id            = $employeeId;
                    $employeeAttendance->date                   = $date;
                    $employeeAttendance->status                 = 'No Working Hour';
                    $employeeAttendance->clock_in               = $time;
                    $employeeAttendance->clock_out              = $time;
                    $employeeAttendance->late                   = '00:00:00';
                    $employeeAttendance->early_leaving          = '00:00:00';
                    $employeeAttendance->overtime               = '00:00:00';
                    $employeeAttendance->total_rest             = '00:00:00';
                    $employeeAttendance->coord_in               = $coord_in;
                    $employeeAttendance->note                   = $note;
                    $employeeAttendance->is_valid               = $is_valid;
                    $employeeAttendance->validate_by            = $is_valid ? 1 : null;
                    $employeeAttendance->attendance_type_id     = $attendance_type;
                    $employeeAttendance->picture_in             = $picture_path;
                    $employeeAttendance->created_by             = \Auth::user()->id;
                    $employeeAttendance->shift_type_id          = $request->shift_type_id;
                    $employeeAttendance->source_in              = 'Application';

                    $logForm =  [
                        'personel_id'   => $employee->personel_id,
                        'date'          => $date,
                        'coordinate'    => $coord_in,
                        'min'           => $time,
                        'max'           => $time,
                        'min_source'    => 'Application',
                        'max_source'    => 'Application',
                        'shift_id'      => $request->shift_type_id,
                    ];

                    LogAttendance::create($logForm);

                    $employeeAttendance->save();

                    return redirect()->route('attendanceemployee.index')->with([
                        'success' => __('Employee Successfully Clock In.'),
                        'employee' => $employee,
                    ]);
                }
                foreach ($checkDb as $check) {
                    $employeeAttendance                         = new AttendanceEmployee();
                    $employeeAttendance->employee_id            = $employeeId;
                    $employeeAttendance->date                   = $date;
                    $employeeAttendance->status                 = 'No Working Hour';
                    $employeeAttendance->clock_in               = $time;
                    $employeeAttendance->clock_out              = $time;
                    $employeeAttendance->late                   = '00:00:00';
                    $employeeAttendance->early_leaving          = '00:00:00';
                    $employeeAttendance->overtime               = '00:00:00';
                    $employeeAttendance->total_rest             = '00:00:00';
                    $employeeAttendance->coord_in               = $coord_in;
                    $employeeAttendance->note                   = $note;
                    $employeeAttendance->is_valid               = $is_valid;
                    $employeeAttendance->validate_by            = $is_valid ? 1 : null;
                    $employeeAttendance->attendance_type_id     = $attendance_type;
                    $employeeAttendance->picture_in             = $picture_path;
                    $employeeAttendance->created_by             = \Auth::user()->id;
                    $employeeAttendance->shift_type_id          = $request->shift_type_id;
                    $employeeAttendance->source_in              = 'Application';

                    $employeeAttendance->save();

                    $logForm =  [
                        'personel_id'   => $employee->personel_id,
                        'date'          => $date,
                        'coordinate'    => $coord_in,
                        'min'           => $time,
                        'max'           => $time,
                        'min_source'    => 'Application',
                        'max_source'    => 'Application',
                        'shift_id'      => $request->shift_type_id,
                    ];

                    LogAttendance::create($logForm);

                    return redirect()->route('attendanceemployee.index')->with([
                        'success' => __('Employee Successfully Clock In.'),
                        'employee' => $employee,
                    ]);
                }
            }
        } else {
            return redirect()->back()->with('error', __('Inactive'));
        }
    }

    public function bulkAttendance(Request $request)
    {
        if (\Auth::user()->can('Create Attendance')) {

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
            
            $branch = $branch_id?->isNotEmpty() ? Branch::whereIn('id', $branch_id)->get()->pluck('name', 'id') : Branch::get()->pluck('name', 'id');
            $branch->prepend('Select Branch', '');

            $department = $branch_id?->isNotEmpty() ? Department::whereIn('branch_id', $branch_id)->get()->pluck('name', 'id') : Department::get()->pluck('name', 'id');
            $department->prepend('Select Department', '');

            $employees = [];
            if (!empty($request->branch) && !empty($request->department)) {
                $employees = Employee::where('is_active', 1)->where('branch_id', $request->branch)->where('department_id', $request->department)->orderby('name', 'asc')->get();
            }


            return view('attendance.bulk', compact('employees', 'branch', 'department'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function bulkAttendanceData(Request $request)
    {
        if (\Auth::user()->can('Create Attendance')) {
            if (!empty($request->branch) && !empty($request->department)) {
                $presentStatus = AttendanceStatus::where('id', 1)->first();
                $date      = $request->date;

                $employees = $request->employee_id;
                $atte      = [];
                foreach ($employees as $employee) {
                    $employee_data = Employee::where('is_active', 1)->find($employee);
                    $shift_times = ShiftTime::where('shift_type_id', $employee_data?->shift_type?->id)
                        ->where('days', date('l', strtotime($date)))
                        ->first();

                    $startTime = strtotime($shift_times->start_time);
                    $endTime = strtotime($shift_times->end_time);

                    if ($startTime > $endTime) {
                        $endTime += 86400;
                    }

                    $present = 'present-' . $employee;
                    $in      = 'in-' . $employee;
                    $out     = 'out-' . $employee;
                    $atte[]  = $present;
                    if ($request->$present == 'on') {

                        $in  = date("H:i:s", strtotime($request->$in));
                        $out = date("H:i:s", strtotime($request->$out));

                        $in_cal = strtotime($in);
                        $out_cal = strtotime($out);
                        if ($in_cal > $out_cal) {
                            $out_cal += 86400;
                        }

                        if ($in_cal > $startTime) {
                            $totalLateSeconds = $in_cal - $startTime;
                            $hours = floor($totalLateSeconds / 3600);
                            $mins  = floor($totalLateSeconds / 60 % 60);
                            $secs  = floor($totalLateSeconds % 60);
                            $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                        } else {
                            $late = '00:00:00';
                        }

                        //early Leaving
                        if ($out_cal < $endTime) {
                            $totalEarlyLeavingSeconds = $endTime - $out_cal;
                            $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                            $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                            $secs                     = floor($totalEarlyLeavingSeconds % 60);
                            $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                        } else {
                            $earlyLeaving = '00:00:00';
                        }


                        if ($out_cal > $endTime) {
                            //Overtime
                            $totalOvertimeSeconds = $out_cal - $endTime;
                            $hours                = floor($totalOvertimeSeconds / 3600);
                            $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                            $secs                 = floor($totalOvertimeSeconds % 60);
                            $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                        } else {
                            $overtime = '00:00:00';
                        }

                        $totalWorkSeconds   = $out_cal - $in_cal;
                        $hours              = floor($totalWorkSeconds / 3600);
                        $mins               = floor($totalWorkSeconds / 60 % 60);
                        $secs               = floor($totalWorkSeconds % 60);
                        $workhours          = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);


                        $attendance = AttendanceEmployee::where('employee_id', '=', $employee)->where('date', '=', $request->date)->first();

                        if (!empty($attendance)) {
                            $employeeAttendance = $attendance;
                        } else {
                            $employeeAttendance              = new AttendanceEmployee();
                            $employeeAttendance->employee_id = $employee;
                            $employeeAttendance->created_by  = \Auth::user()->id;
                        }


                        $employeeAttendance->date                    = $request->date;
                        $employeeAttendance->status                  = 'Present';
                        $employeeAttendance->clock_in                = $in;
                        $employeeAttendance->clock_out               = $out;
                        $employeeAttendance->late                    = $late;
                        $employeeAttendance->early_leaving           = $earlyLeaving;
                        $employeeAttendance->overtime                = $overtime;
                        $employeeAttendance->work_hours              = $workhours;
                        $employeeAttendance->total_rest              = '00:00:00';
                        $employeeAttendance->attendance_status_id    = $presentStatus->id;
                        $employeeAttendance->shift_type_id           = $employee_data->shift_type_id;
                        $employeeAttendance->save();
                    }
                    // else {
                    //     $attendance = AttendanceEmployee::where('employee_id', '=', $employee)->where('date', '=', $request->date)->first();

                    //     if (!empty($attendance)) {
                    //         $employeeAttendance = $attendance;
                    //     } else {
                    //         $employeeAttendance              = new AttendanceEmployee();
                    //         $employeeAttendance->employee_id = $employee;
                    //         $employeeAttendance->created_by  = \Auth::user()->creatorId();
                    //     }

                    //     $employeeAttendance->status        = 'Leave';
                    //     $employeeAttendance->date          = $request->date;
                    //     $employeeAttendance->shift_type_id = $request->date;
                    //     $employeeAttendance->clock_in      = '00:00:00';
                    //     $employeeAttendance->clock_out     = '00:00:00';
                    //     $employeeAttendance->late          = '00:00:00';
                    //     $employeeAttendance->early_leaving = '00:00:00';
                    //     $employeeAttendance->overtime      = '00:00:00';
                    //     $employeeAttendance->total_rest    = '00:00:00';
                    //     $employeeAttendance->save();
                    // }
                }

                return redirect()->back()->with('success', __('Employee attendance successfully created.'));
            } else {
                return redirect()->back()->with('error', __('Branch & department field required.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function validateAttendance($id)
    {
        if (\Auth::user()->can('Edit Attendance')) {
            $attendance = AttendanceEmployee::where('id', $id)->first();

            $attendance->is_valid = true;
            $attendance->validate_by = \Auth::user()->id;
            $attendance->save();

            return redirect()->back()->with('success', __('Attendance successfully validated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function export(Request $request)
    {
        $urlQuery = parse_url($request->url, PHP_URL_QUERY);
        $queryArray = [];
        if (!empty($urlQuery)) {
            foreach (explode('&', $urlQuery) as $query) {
                list($key, $value) = explode('=', $query);
                $queryArray[$key] = $value;
            }
        }

        $name = 'Attendance-Employee' . date('Y-m-d H:i:s');
        $data = Excel::download(new AttendanceMultipleExport(json_encode($queryArray)), $name . '.xlsx');

        return $data;
    }

    public function exportNotClockIn(Request $request) {
        $date = $request->date ?? date('Y-m-d');

        $name = 'Not-Clock-In_Employee' . date('Y-m-d H:i:s');
        $data = Excel::download(new NotClockInExport($date), $name . '.xlsx');

        return $data;
    }
}
