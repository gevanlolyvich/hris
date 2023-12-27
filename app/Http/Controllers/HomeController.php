<?php

namespace App\Http\Controllers;

use App\Models\AccountList;
use App\Models\Announcement;
use App\Models\AttendanceEmployee;
use App\Models\AttendanceType;
use App\Models\Employee;
use App\Models\Event;
use App\Models\LandingPageSection;
use App\Models\Meeting;
use App\Models\Job;
use App\Models\Payees;
use App\Models\Payer;
use App\Models\ShiftTime;
use App\Models\ShiftType;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->type == 'employee') {

                $emp = Employee::where('user_id', $user->id)->first();

                $announcements = Announcement::orderBy('announcements.id', 'desc')->take(5)->leftjoin('announcement_employees', 'announcements.id', '=', 'announcement_employees.announcement_id')->where('announcement_employees.employee_id', '=', $emp->id)->orWhere(
                    function ($q) {
                        $q->where('announcements.department_id', '["0"]')->whereOr('announcements.employee_id', '["0"]');
                    }
                )->get();

                $employees = Employee::orderby('name', 'asc')->get();
                $meetings  = Meeting::orderBy('meetings.id', 'desc')->take(5)->leftjoin('meeting_employees', 'meetings.id', '=', 'meeting_employees.meeting_id')->where('meeting_employees.employee_id', '=', $emp->id)->orWhere(
                    function ($q) {
                        $q->where('meetings.department_id', '["0"]')->where('meetings.employee_id', '["0"]');
                    }
                )->get();

                // $events    = Event::select('events.*', 'events.id as event_id_pk', 'event_employees.*')
                //     ->leftjoin('event_employees', 'events.id', '=', 'event_employees.event_id')
                //     ->where('event_employees.employee_id', '=', $emp->id)
                //     ->orWhere(
                //         function ($q) {
                //             $q->where('events.department_id', '["0"]')->where('events.employee_id', '["0"]');
                //         }
                //     )->get();

                // $arrEvents = [];
                // foreach ($events as $event) {

                //     $arr['id']              = $event['id'];
                //     $arr['title']           = $event['title'];
                //     $arr['start']           = $event['start_date'];
                //     $arr['end']             = $event['end_date'];
                //     $arr['className']       = $event['color'];
                //     // $arr['borderColor']     = "#fff";
                //     $arr['url']             = route('eventsshow', (!empty($event['event_id_pk'])) ? $event['event_id_pk'] : '');
                //     // $arr['textColor']       = "white";

                //     $arrEvents[] = $arr;
                // }

                $date               = date("Y-m-d");
                $dateYesterday      = date("Y-m-d", strtotime('yesterday'));
                $time               = date("H:i:s");
                $employeeAttendance = AttendanceEmployee::orderBy('id', 'desc')->where('employee_id', '=', !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0)->where('date', '=', $date)->first();
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

                return view('dashboard.dashboard', compact('announcements', 'employees', 'meetings', 'employeeAttendance', 'yesterdayEmployeeAttendance', 'officeTime', 'yesterdayOfficeTime', 'attendance_type'));
            } else {
                // $events    = Event::where('created_by', '=', \Auth::user()->creatorId())->get();
                // $arrEvents = [];

                // foreach ($events as $event) {
                //     $arr['id']    = $event['id'];
                //     $arr['title'] = $event['title'];
                //     $arr['start'] = $event['start_date'];
                //     $arr['end']   = $event['end_date'];

                //     $arr['className'] = $event['color'];
                //     // $arr['borderColor']     = "#fff";
                //     // $arr['textColor']       = "white";
                //     $arr['url']             = route('event.edit', $event['id']);

                //     $arrEvents[] = $arr;
                // }



                $announcements = Announcement::orderBy('announcements.id', 'desc')->take(5)->where('created_by', '=', \Auth::user()->creatorId())->get();


                $emp           = User::where('type', '=', 'employee')->where('created_by', '=', \Auth::user()->creatorId())->get();
                $countEmployee = count($emp);

                $user      = User::where('type', '!=', 'employee')->where('created_by', '=', \Auth::user()->creatorId())->get();
                $countUser = count($user);

                $countTicket      = Ticket::where('created_by', '=', \Auth::user()->creatorId())->count();
                $countOpenTicket  = Ticket::where('status', '=', 'open')->where('created_by', '=', \Auth::user()->creatorId())->count();
                $countCloseTicket = Ticket::where('status', '=', 'close')->where('created_by', '=', \Auth::user()->creatorId())->count();

                $currentDate = date('Y-m-d');

                $employees     = Employee::where('is_active', 1)->where('created_by', '=', \Auth::user()->creatorId())->get();
                $countEmployee = count($employees);
                $notClockIn    = AttendanceEmployee::where('date', '=', $currentDate)->get()->pluck('employee_id');

                $notClockIns    = Employee::where('is_active', 1)->where('created_by', '=', \Auth::user()->creatorId())->whereNotIn('id', $notClockIn)->orderBy('name', 'asc')->get();
                $accountBalance = AccountList::where('created_by', '=', \Auth::user()->creatorId())->sum('initial_balance');

                $activeJob   = Job::where('status', 'active')->where('created_by', '=', \Auth::user()->creatorId())->count();
                $inActiveJOb = Job::where('status', 'in_active')->where('created_by', '=', \Auth::user()->creatorId())->count();

                $totalPayee = Payees::where('created_by', '=', \Auth::user()->creatorId())->count();
                $totalPayer = Payer::where('created_by', '=', \Auth::user()->creatorId())->count();

                $meetings = Meeting::where('created_by', '=', \Auth::user()->creatorId())->limit(5)->get();

                return view('dashboard.dashboard', compact('announcements', 'employees', 'activeJob', 'inActiveJOb', 'meetings', 'countEmployee', 'countUser', 'countTicket', 'countOpenTicket', 'countCloseTicket', 'notClockIns', 'countEmployee', 'accountBalance', 'totalPayee', 'totalPayer'));
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

    public function getOrderChart($arrParam)
    {
        $arrDuration = [];
        if ($arrParam['duration']) {
            if ($arrParam['duration'] == 'week') {
                $previous_week = strtotime("-2 week +1 day");
                for ($i = 0; $i < 14; $i++) {
                    $arrDuration[date('Y-m-d', $previous_week)] = date('d-M', $previous_week);
                    $previous_week                              = strtotime(date('Y-m-d', $previous_week) . " +1 day");
                }
            }
        }

        $arrTask          = [];
        $arrTask['label'] = [];
        $arrTask['data']  = [];
        foreach ($arrDuration as $date => $label) {

            $data               = \Order::select(\DB::raw('count(*) as total'))->whereDate('created_at', '=', $date)->first();
            $arrTask['label'][] = $label;
            $arrTask['data'][]  = $data->total;
        }

        return $arrTask;
    }
}
