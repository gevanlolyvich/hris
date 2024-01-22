<?php

namespace App\Http\Controllers;

use App\Models\AccountList;
use App\Models\Announcement;
use App\Models\AttendanceEmployee;
use App\Models\AttendanceType;
use App\Models\Employee;
use App\Models\Event;
use App\Models\Termination;
use App\Models\LandingPageSection;
use App\Models\Meeting;
use App\Models\Job;
use App\Models\Leave;
use App\Models\Payees;
use App\Models\Payer;
use App\Models\Permit;
use App\Models\AttendanceRequest;
use App\Models\ShiftTime;
use App\Models\ShiftType;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Utility;
use App\Models\Overtime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the application dashboard.
     *
    //  * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (Auth::check()) {
            $user = Auth::user();
            // Get today's date
            $today = Carbon::today()->toDateString();
            $settings       = Utility::settings();
            if ($user->type == 'employee') {
                $emp            = Employee::where('user_id', $user->id)->first();

                $overtime       = Overtime::where('employee_id', $emp->id)->where('date', date('Y-m-d'))->first();

                $shift_types    = ShiftType::where('branch_id', $emp->branch_id)->get()->pluck('name', 'id');

                $subordinates   = \Auth::user()->employee->subordinatesFlatten();

                $employees_id   = collect();
                // Check if employee managing other employee or not
                if ($subordinates->isNotEmpty()) {

                    foreach ($subordinates as $subordinate) {
                        $employees_id->push($subordinate->id);
                    }
                }
                $employees_id->push($emp->id);

                $announcements = Announcement::orderBy('announcements.id', 'desc')->take(5)->leftjoin('announcement_employees', 'announcements.id', '=', 'announcement_employees.announcement_id')->where('announcement_employees.employee_id', '=', $emp->id)->orWhere(
                    function ($q) {
                        $q->where('announcements.department_id', '["0"]')->whereOr('announcements.employee_id', '["0"]');
                    }
                )->get();

                $terminations = Termination::whereIn('employee_id', $employees_id)->where('notice_date', '<=', $today)->where('termination_date', '>=', $today)->get();

                foreach ($terminations as $termination) {

                    // Create a new "Announcement-like" structure for the termination
                    $terminationAsAnnouncement = new Announcement([
                        'title' => $termination?->employee?->name . ' | ' . $termination?->terminationType?->name,
                        'start_date' => $termination->notice_date,
                        'end_date' => $termination->termination_date,
                        'description' => $termination->description ?? '-',
                    ]);

                    $announcements->push($terminationAsAnnouncement);
                }

                $announcements = $announcements->sortByDesc('start_date');

                $employees = !empty(\Auth::user()->branch_id) ? Employee::where('branch_id', \Auth::user()->branch_id)->orderby('name', 'asc')->get() : Employee::orderby('name', 'asc')->get();
                $meetings  = !empty(\Auth::user()->branch_id) ? Meeting::where('branch_id', \Auth::user()->branch_id)->orderby('start_time', 'DESC')->take(5)->leftjoin('meeting_employees', 'meetings.id', '=', 'meeting_employees.meeting_id')->where('meeting_employees.employee_id', '=', $emp->id)->orWhere(
                    function ($q) {
                        $q->where('meetings.department_id', '["0"]')->where('meetings.employee_id', '["0"]');
                    }
                )->get() : Meeting::orderBy('meetings.id', 'desc')->take(5)->leftjoin('meeting_employees', 'meetings.id', '=', 'meeting_employees.meeting_id')->where('meeting_employees.employee_id', '=', $emp->id)->orWhere(
                    function ($q) {
                        $q->where('meetings.department_id', '["0"]')->where('meetings.employee_id', '["0"]');
                    }
                )->get();


                $date               = date("Y-m-d");
                $dateYesterday      = date("Y-m-d", strtotime('yesterday'));
                $time               = date("H:i:s");
                $employeeAttendance = AttendanceEmployee::orderBy('id', 'desc')->where('employee_id', '=', !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0)->where('date', '=', $date)->first();
                // return $employeeAttendance;
                $yesterdayEmployeeAttendance = AttendanceEmployee::orderBy('id', 'desc')->where('employee_id', '=', !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0)->where('date', '=', $dateYesterday)->first();

                $shift_times = ShiftTime::where('shift_type_id', \Auth::user()->employee->shift_type->id)
                    ->where('days', date('l'))
                    ->first();
                $shift_type = ShiftType::where('id', \Auth::user()->employee->shift_type->id)
                    ->first();

                $officeTime['startTime']    = $shift_times->start_time;
                $officeTime['endTime']      = $shift_times->end_time;
                $officeTime['name']         = $shift_type->name;
                $officeTime['is_working']   = $shift_times->is_working;
                $officeTime['is_cross_day'] = $shift_times->start_time > $shift_times->end_time ? true : false;

                // calculate default clock out for yesterday cross day shift
                $clockoutSeconds                          = strtotime($shift_times->end_time) - strtotime($date);
                $hours                                    = floor($clockoutSeconds / 3600);
                $mins                                     = floor($clockoutSeconds / 60 % 60);
                $secs                                     = floor($clockoutSeconds % 60);
                $default_clock_out_cross_day              = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                $today_absolute_out_time                  = sprintf('%02d:%02d:%02d', $hours + 1, $mins, $secs);
                $officeTime['default_clock_out']          = $default_clock_out_cross_day;
                $officeTime['absolute_out']               = strtotime("$date $today_absolute_out_time");

                // create shift and office time for yesterday
                $yesterday_shift_times = ShiftTime::where('shift_type_id', \Auth::user()->employee->shift_type->id)
                    ->where('days', date('l', strtotime('yesterday')))
                    ->first();

                $yesterdayOfficeTime['startTime']    = $yesterday_shift_times->start_time;
                $yesterdayOfficeTime['endTime']      = $yesterday_shift_times->end_time;
                $yesterdayOfficeTime['is_working']   = $yesterday_shift_times->is_working;
                $yesterdayOfficeTime['is_cross_day'] = $yesterday_shift_times->start_time > $yesterday_shift_times->end_time ? true : false;

                // calculate default clock out for yesterday cross day shift
                $clockoutSeconds                          = strtotime($yesterday_shift_times->end_time) - strtotime($date);
                $hours                                    = floor($clockoutSeconds / 3600);
                $mins                                     = floor($clockoutSeconds / 60 % 60);
                $secs                                     = floor($clockoutSeconds % 60);
                $default_clock_out_cross_day              = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                $yesterday_absolute_out_time              = sprintf('%02d:%02d:%02d', $hours + 1, $mins, $secs);
                $yesterdayOfficeTime['default_clock_out'] = $default_clock_out_cross_day;
                $yesterdayOfficeTime['absolute_out']      = strtotime("$date $yesterday_absolute_out_time");

                // get all attendance type
                $attendance_type        = AttendanceType::where('id', '!=', 4)->get()->pluck('name', 'id');

                return view('dashboard.dashboard', compact('announcements', 'employees', 'meetings', 'employeeAttendance', 'yesterdayEmployeeAttendance', 'officeTime', 'yesterdayOfficeTime', 'attendance_type', 'settings', 'overtime', 'shift_types'));
            } else {

                $announcements = !empty(\Auth::user()->branch_id) ? Announcement::where('branch_id', \Auth::user()->branch_id)->orderBy('announcements.id', 'desc')->take(5)->get() : Announcement::orderBy('announcements.id', 'desc')->take(5)->get();

                $emp           = !empty(\Auth::user()->branch_id) ? User::whereHas('employee', function ($query) {
                    $query->where('branch_id', \Auth::user()->branch_id);
                })
                    ->where('type', '=', 'employee')
                    ->get()
                    : User::where('type', '=', 'employee')
                    ->get();
                $countEmployee = count($emp);

                $user      = !empty(\Auth::user()->branch_id) ? User::where('branch_id', \Auth::user()->branch_id)->where('type', '!=', 'employee')->get() : User::where('type', '!=', 'employee')->get();
                $countUser = count($user);

                $currentDate = date('Y-m-d');

                $employees          = !empty(\Auth::user()->branch_id) ? Employee::where('branch_id', \Auth::user()->branch_id)->where('is_active', 1)->get() : Employee::where('is_active', 1)->get();
                $countEmployee      = count($employees);
                $notClockIn         = !empty(\Auth::user()->branch_id) ? AttendanceEmployee::whereHas('employee', function ($query) {
                    $query->where('branch_id', \Auth::user()->branch_id);
                })->where('date', '=', $currentDate)->get()->pluck('employee_id') : AttendanceEmployee::where('date', '=', $currentDate)->get()->pluck('employee_id');
                $validAttendance    = !empty(\Auth::user()->branch_id) ? AttendanceEmployee::whereHas('employee', function ($query) {
                    $query->where('branch_id', \Auth::user()->branch_id);
                })->where('date', '=', $currentDate)->where('is_valid', true)->count() : AttendanceEmployee::where('date', '=', $currentDate)->where('is_valid', true)->count();
                $invalidAttendance  = !empty(\Auth::user()->branch_id) ? AttendanceEmployee::whereHas('employee', function ($query) {
                    $query->where('branch_id', \Auth::user()->branch_id);
                })->where('date', '=', $currentDate)->whereNull('is_valid')->count() : AttendanceEmployee::where('date', '=', $currentDate)->whereNull('is_valid')->count();

                $requestAttendanceCount = !empty(\Auth::user()->branch_id) ? AttendanceRequest::whereHas('employee', function ($query) {
                    $query->where('branch_id', \Auth::user()->branch_id);
                })->whereNull('is_approved')->count() : AttendanceRequest::whereNull('is_approved')->count();
                $permitCount            = !empty(\Auth::user()->branch_id) ? Permit::whereHas('employee', function ($query) {
                    $query->where('branch_id', \Auth::user()->branch_id);
                })->whereNull('is_approved')->count() : Permit::whereNull('is_approved')->count();
                $leaveCount             = !empty(\Auth::user()->branch_id) ? Leave::whereHas('employees', function ($query) {
                    $query->where('branch_id', \Auth::user()->branch_id);
                })->where('status', 'Pending')->count() : Leave::where('status', 'Pending')->count();

                $notClockIns    = !empty(\Auth::user()->branch_id) ? Employee::where('branch_id', \Auth::user()->branch_id)->where('is_active', 1)->whereNotIn('id', $notClockIn)->orderBy('name', 'asc')->get() : Employee::where('is_active', 1)->whereNotIn('id', $notClockIn)->orderBy('name', 'asc')->get();
                $accountBalance = AccountList::sum('initial_balance');

                $activeJob   = Job::where('status', 'active')->count();
                $inActiveJOb = Job::where('status', 'in_active')->count();

                $totalPayee = Payees::count();
                $totalPayer = Payer::count();

                $meetings = !empty(\Auth::user()->branch_id) ? Meeting::where('branch_id', \Auth::user()->branch_id)->orderby('start_time', 'DESC')->limit(5)->get() : Meeting::orderby('start_time', 'DESC')->limit(5)->get();

                $employees_id = $employees->pluck('id');
                $terminations = Termination::whereIn('employee_id', $employees_id)->where('notice_date', '<=', $today)->where('termination_date', '>=', $today)->get();

                foreach ($terminations as $termination) {

                    // Create a new "Announcement-like" structure for the termination
                    $terminationAsAnnouncement = new Announcement([
                        'title' => $termination?->employee?->name . ' | ' . $termination?->terminationType?->name,
                        'start_date' => $termination->notice_date,
                        'end_date' => $termination->termination_date,
                        'description' => $termination->description ?? '-',
                    ]);

                    $announcements->push($terminationAsAnnouncement);
                }

                $announcements = $announcements->sortByDesc('start_date');

                return view('dashboard.dashboard', compact('announcements', 'employees', 'activeJob', 'inActiveJOb', 'meetings', 'countEmployee', 'countUser', 'notClockIns', 'countEmployee', 'accountBalance', 'totalPayee', 'totalPayer', 'validAttendance', 'invalidAttendance', 'requestAttendanceCount', 'permitCount', 'leaveCount', 'settings'));
            }
        } else {
            if (!file_exists(storage_path() . "/installed")) {
                header('location:install');
                die;
            } else {
                $settings = Utility::settings();
                if ($settings['display_landing_page'] == 'on') {
                    $get_section = LandingPageSection::orderBy('section_order', 'ASC')->get();
                    return view('layouts.landing', compact('get_section'));
                } else {
                    return redirect('login');
                }
            }
        }
    }

    // public function getOrderChart($arrParam)
    // {
    //     $arrDuration = [];
    //     if ($arrParam['duration']) {
    //         if ($arrParam['duration'] == 'week') {
    //             $previous_week = strtotime("-2 week +1 day");
    //             for ($i = 0; $i < 14; $i++) {
    //                 $arrDuration[date('Y-m-d', $previous_week)] = date('d-M', $previous_week);
    //                 $previous_week                              = strtotime(date('Y-m-d', $previous_week) . " +1 day");
    //             }
    //         }
    //     }

    //     $arrTask          = [];
    //     $arrTask['label'] = [];
    //     $arrTask['data']  = [];
    //     foreach ($arrDuration as $date => $label) {

    //         $data               = \Order::select(\DB::raw('count(*) as total'))->whereDate('created_at', '=', $date)->first();
    //         $arrTask['label'][] = $label;
    //         $arrTask['data'][]  = $data->total;
    //     }

    //     return $arrTask;
    // }
}
