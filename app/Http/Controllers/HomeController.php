<?php

namespace App\Http\Controllers;

use App\Models\AccountList;
use App\Models\Announcement;
use App\Models\AttendanceEmployee;
use App\Models\AttendanceType;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Event;
use App\Models\Termination;
use App\Models\LandingPageSection;
use App\Models\Meeting;
use App\Models\Job;
use App\Models\Leave;
use App\Models\LeaveOffice;
use App\Models\Payees;
use App\Models\Payer;
use App\Models\Permit;
use App\Models\AttendanceRequest;
use App\Models\EmployeePeriod;
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
    public function __construct() {}

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
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
                $attendances    = AttendanceEmployee::where('employee_id', $emp->id)->where('date', date('Y-m-d'))->orderBy('id', 'ASC')->get();
                $isShiftEmployee = (bool) $emp->is_shift;

                $approvedLeaveToday = \App\Models\Leave::where('employee_id', $emp->id)
                    ->where('status', 'Approved')
                    ->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today)
                    ->exists();

                $officeTime           = [];
                $yesterdayOfficeTime  = [];
                $scheduledShifts      = collect();
                $currentShift         = null;

                // $shift_types    = ShiftType::where('branch_id', $emp->branch_id)->get()->pluck('name', 'id');
                $branch = Branch::find($emp->branch_id);
                $branch_id = collect();
                if ($branch) {
                    $branch_id->push($branch?->id);
                }

                $parents = $branch?->parentBranchFlatten();
                if ($parents?->isNotEmpty()) {
                    foreach ($parents as $parent) {
                        $branch_id->push($parent->id);
                    }
                }

                $shift_types    = ShiftType::whereIn('branch_id', $branch_id)
                    ->with(['shiftTimes' => function ($query) {
                        $query->where('days', date('l'));
                    }])
                    ->whereHas('shiftTimes', function ($q) {
                        $q->where('days', date('l'));
                    })->get();

                // return $shift_types;
                for ($i = 0; $i < count($shift_types); $i++) {
                    $today_shift_times = ShiftTime::where('shift_type_id', $shift_types[$i]->id)
                        ->where('days', date('l'))->first();

                    if ($today_shift_times->is_working) {
                        $shift_types[$i]['name'] = substr($today_shift_times['start_time'], 0, 5) . '-' . substr($today_shift_times['end_time'], 0, 5) . ' | ' . $shift_types[$i]['name'];
                    } else {
                        $shift_types[$i]['name'] = __('Holidays') . ' | ' . $shift_types[$i]['name'];
                    }
                }
                // return $shift_types;
                $shift_types    = $shift_types->pluck('name', 'id')->toArray();

                $subordinates   = \Auth::user()->employee->subordinatesFlatten();

                $employees_id   = collect();
                // Check if employee managing other employee or not
                if ($subordinates->isNotEmpty()) {

                    foreach ($subordinates as $subordinate) {
                        $employees_id->push($subordinate->id);
                    }
                }
                $employees_id->push($emp->id);

                $announcements = Announcement::orderBy('announcements.id', 'desc')->take(5)->leftjoin('announcement_employees', 'announcements.id', '=', 'announcement_employees.announcement_id')->where('announcement_employees.employee_id', $emp->id)->where('announcements.start_date', '<=', date('Y-m-d'))->where('announcements.end_date', '>=', date('Y-m-d'))->get();

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

                foreach ($meetings as $meeting) {
                    $meeting->id =  $meeting->meeting_id;
                }

                $date               = date("Y-m-d");
                $dateYesterday      = date("Y-m-d", strtotime('yesterday'));
                $time               = date("H:i:s");
                $employeeAttendance = AttendanceEmployee::orderBy('id', 'desc')->where('employee_id', !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0)->where('date', $date)->first();
                // return $employeeAttendance;
                $yesterdayEmployeeAttendance = AttendanceEmployee::orderBy('id', 'desc')->where('employee_id', !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0)->where('date', $dateYesterday)->first();

                if (!$emp->is_shift) {
                $shift_times = ShiftTime::where('shift_type_id', \Auth::user()?->employee?->shift_type?->id)
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
                $yesterday_shift_times = ShiftTime::where('shift_type_id', $yesterdayEmployeeAttendance?->shift_type_id)
                    ->where('days', date('l', strtotime('yesterday')))
                    ->first();

                $yesterdayOfficeTime['startTime']    = $yesterday_shift_times?->start_time;
                $yesterdayOfficeTime['endTime']      = $yesterday_shift_times?->end_time;
                $yesterdayOfficeTime['is_working']   = $yesterday_shift_times?->is_working;
                $yesterdayOfficeTime['is_cross_day'] = $yesterday_shift_times?->start_time > $yesterday_shift_times?->end_time ? true : false;

                // calculate default clock out for yesterday cross day shift
                $clockoutSeconds                          = strtotime($yesterday_shift_times?->end_time) - strtotime($date);
                $hours                                    = floor($clockoutSeconds / 3600);
                $mins                                     = floor($clockoutSeconds / 60 % 60);
                $secs                                     = floor($clockoutSeconds % 60);
                $default_clock_out_cross_day              = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                $yesterday_absolute_out_time              = sprintf('%02d:%02d:%02d', $hours + 1, $mins, $secs);
                $yesterdayOfficeTime['default_clock_out'] = $default_clock_out_cross_day;
                $yesterdayOfficeTime['absolute_out']      = strtotime("$date $yesterday_absolute_out_time");
                } elseif ($emp->is_shift) {
                    $scheduledShifts = collect();
                    $currentShift = null;
                    $now = time();

                    // Build a normalized shift window + its attendance for a roster schedule.
                    // Attendance for a shift is always recorded on the shift's start date (schedule->date).
                    $buildShiftEntry = function ($schedule, $attendance) {
                        $stimes = ShiftTime::where('shift_type_id', $schedule->shift_type_id)
                            ->where('days', date('l', strtotime($schedule->date)))
                            ->first();

                        return [
                            'shift_type_id'      => $schedule->shift_type_id,
                            'name'               => $schedule->shiftType?->name ?? '-',
                            'start_time'         => $stimes?->start_time,
                            'end_time'           => $stimes?->end_time,
                            'is_working'         => (bool) ($stimes?->is_working ?? 0),
                            'is_cross_day'       => $stimes && $stimes->start_time > $stimes->end_time ? true : false,
                            'shift_date'         => $schedule->date,
                            'end_date'           => $schedule->end_date ?? $schedule->date,
                            'attendance'         => $attendance,
                        ];
                    };

                    // Active roster schedules whose window covers or relates to today
                    // (schedules starting today, or cross-day schedules that started earlier
                    // and are still running, e.g. Shift 3 on 28 ends on the morning of 29).
                    $rosteredSchedules = $emp->shiftSchedules()
                        ->where('date', '<=', $date)
                        ->where(function ($q) use ($date) {
                            $q->where('end_date', '>=', $date)
                                ->orWhereNull('end_date');
                        })
                        ->orderBy('date', 'ASC')
                        ->orderBy('id', 'ASC')
                        ->get();

                    foreach ($rosteredSchedules as $schedule) {
                        $shiftDate = $schedule->date;
                        $endDate = $schedule->end_date ?? $shiftDate;

                        // Only consider schedules where today is within [date, end_date].
                        if ($date < $shiftDate || $date > $endDate) {
                            continue;
                        }

                        $attendance = AttendanceEmployee::where('employee_id', $emp->id)
                            ->where('date', $shiftDate)
                            ->where('shift_type_id', $schedule->shift_type_id)
                            ->first();

                        $scheduledShifts->push($buildShiftEntry($schedule, $attendance));
                    }

                    $scheduledShifts = $scheduledShifts->values();

                    // A permit/permission placeholder row (clock_in == '00:00:00') represents an
                    // approved permit, not a real clock-in. The employee must still be able to
                    // clock in for that shift, so treat it as "no attendance yet".
                    $isPermitPlaceholder = function ($attendance) {
                        return !empty($attendance) && $attendance->clock_in == '00:00:00';
                    };

                    // An un-clocked-out shift always takes priority so the employee can finish it first.
                    $pendingShift = $scheduledShifts->first(function ($s) use ($isPermitPlaceholder) {
                        return !empty($s['attendance']) && !$isPermitPlaceholder($s['attendance']) && ($s['attendance']->clock_out == '00:00:00' || $s['attendance']->clock_out == $s['attendance']->clock_in);
                    });

                    if ($pendingShift) {
                        // Belum clock-out: tampilkan tombol clock-out (wajib diselesaikan dulu).
                        $currentShift = $pendingShift;
                    } else {
                        // Tidak ada yang menunggu clock-out.
                        // 1) Shift yang sedang aktif & BELUM ada attendance → tombol clock-in.
                        $activeNoAttendance = $this->resolveCurrentShift($scheduledShifts, $now, function ($s) use ($isPermitPlaceholder) {
                            return empty($s['attendance']) || $isPermitPlaceholder($s['attendance']);
                        });

                        if ($activeNoAttendance) {
                            $currentShift = $activeNoAttendance;
                        } else {
                            // 2) Shift yang sedang aktif & SUDAH ada attendance (sudah clock-out)
                            //    → tombol clock-out tetap tampil agar bisa diklik ulang.
                            //    Pilih yang attendance-nya terbaru agar menunjuk ke shift yang
                            //    sedang/baru dikerjakan (menghindari tertuju ke shift lama saat overlap).
                            $currentShift = $this->resolveCurrentShift($scheduledShifts, $now, function ($s) {
                                return !empty($s['attendance']);
                            }, true);
                        }
                    }
                }

                // get all attendance type
                $attendance_type        = AttendanceType::where('id', '!=', 4)->get()->pluck('name', 'id');

                return view('dashboard.dashboard', compact('announcements', 'employees', 'meetings', 'employeeAttendance', 'yesterdayEmployeeAttendance', 'officeTime', 'yesterdayOfficeTime', 'attendance_type', 'settings', 'overtime', 'shift_types', 'attendances', 'isShiftEmployee', 'scheduledShifts', 'currentShift', 'approvedLeaveToday'));
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

                $announcements = $branch_id?->isNotEmpty() ? Announcement::whereIn('branch_id', $branch_id)->orderBy('start_date', 'desc') : Announcement::orderBy('start_date', 'desc');
                $announcements = $announcements->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->get();

                $emp           = $branch_id?->isNotEmpty() ? User::whereHas('employee', function ($query) use ($branch_id) {
                    $query->whereIn('branch_id', $branch_id);
                })
                    ->where('type', '=', 'employee')
                    ->get()
                    : User::where('type', '=', 'employee')
                    ->get();
                $countEmployee = count($emp);

                $user      = $branch_id?->isNotEmpty() ? User::whereIn('branch_id', $branch_id)->where('type', '!=', 'employee')->get() : User::where('type', '!=', 'employee')->get();
                $countUser = count($user);

                $currentDate = date('Y-m-d');

                $employees          = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->where('is_active', 1)->get() : Employee::where('is_active', 1)->get();
                $countEmployee      = count($employees);
                $notClockIn         = $branch_id?->isNotEmpty() ? AttendanceEmployee::whereHas('employee', function ($query) use ($branch_id) {
                    $query->whereIn('branch_id', $branch_id);
                })->where('date', '=', $currentDate)->get()->pluck('employee_id') : AttendanceEmployee::where('date', '=', $currentDate)->get()->pluck('employee_id');
                $validAttendance    = $branch_id?->isNotEmpty() ? AttendanceEmployee::whereHas('employee', function ($query) use ($branch_id) {
                    $query->whereIn('branch_id', $branch_id);
                })->where('date', '=', $currentDate)->where('is_valid', true)->count() : AttendanceEmployee::where('date', '=', $currentDate)->where('is_valid', true)->count();
                $invalidAttendance  = $branch_id?->isNotEmpty() ? AttendanceEmployee::whereHas('employee', function ($query) use ($branch_id) {
                    $query->whereIn('branch_id', $branch_id);
                })->where('date', '=', $currentDate)->whereNull('is_valid')->count() : AttendanceEmployee::where('date', '=', $currentDate)->whereNull('is_valid')->count();

                $requestAttendanceCount = $branch_id?->isNotEmpty() ? AttendanceRequest::whereHas('employee', function ($query) use ($branch_id) {
                    $query->whereIn('branch_id', $branch_id);
                })->whereNull('is_approved')->count() : AttendanceRequest::whereNull('is_approved')->count();

                $permitCount            = $branch_id?->isNotEmpty() ? Permit::whereHas('employee', function ($query) use ($branch_id) {
                    $query->whereIn('branch_id', $branch_id);
                })->whereNull('is_approved')->count() : Permit::whereNull('is_approved')->count();

                $leaveCount             = $branch_id?->isNotEmpty() ? Leave::whereHas('employees', function ($query) use ($branch_id) {
                    $query->whereIn('branch_id', $branch_id);
                })->where('status', 'Pending')->count() : Leave::where('status', 'Pending')->count();

                $leaveOfficeCount       = $branch_id?->isNotEmpty() ? LeaveOffice::whereHas('employee', function ($query) use ($branch_id, $today) {
                    $query->whereIn('branch_id', $branch_id);
                })->where('date', $today)->count() : LeaveOffice::where('date', $today)->count();

                $notClockIns    = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->where('is_active', 1)->whereNotIn('id', $notClockIn)->orderBy('name', 'asc')->get() : Employee::where('is_active', 1)->whereNotIn('id', $notClockIn)->orderBy('name', 'asc')->get();
                $accountBalance = AccountList::sum('initial_balance');

                $pendingEmployeeApplicationsCount = $branch_id?->isNotEmpty() ? EmployeePeriod::whereHas('employee', function ($query) use ($branch_id) {
                    $query->whereIn('branch_id', $branch_id);
                })->where('status', 'Pending')->count() : EmployeePeriod::where('status', 'Pending')->count();

                $rejectEmployeeApplicationsCount = $branch_id?->isNotEmpty() ? EmployeePeriod::whereHas('employee', function ($query) use ($branch_id) {
                    $query->whereIn('branch_id', $branch_id);
                })->where('status', 'Reject')->count() : EmployeePeriod::where('status', 'Reject')->count();

                $activeJob   = Job::where('status', 'active')->count();
                $inActiveJOb = Job::where('status', 'in_active')->count();

                $totalPayee = Payees::count();
                $totalPayer = Payer::count();

                $meetings = $branch_id?->isNotEmpty() ? Meeting::whereIn('branch_id', $branch_id)->orderby('start_time', 'DESC')->limit(5)->get() : Meeting::orderby('start_time', 'DESC')->limit(5)->get();

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

                // $announcements = $announcements->sortByDesc('start_date');

                return view('dashboard.dashboard', compact(
                    'announcements',
                    'employees',
                    'activeJob',
                    'inActiveJOb',
                    'meetings',
                    'countEmployee',
                    'countUser',
                    'notClockIns',
                    'countEmployee',
                    'accountBalance',
                    'totalPayee',
                    'totalPayer',
                    'validAttendance',
                    'invalidAttendance',
                    'requestAttendanceCount',
                    'permitCount',
                    'leaveCount',
                    'leaveOfficeCount',
                    'settings',
                    'rejectEmployeeApplicationsCount',
                    'pendingEmployeeApplicationsCount'
                ));
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

    /**
     * Resolve the roster shift that is currently active based on the current time.
     *
     * A shift already clocked-in but not clocked-out is prioritized so the employee
     * can finish it first before moving on to the next scheduled shift.
     *
     * @param \Illuminate\Support\Collection $scheduledShifts
     * @param int|null $now
     * @return array|null
     */
    private function resolveCurrentShift($scheduledShifts, $now = null, $filter = null, $sortByAttendanceDesc = false)
    {
        $now = $now ?: time();

        $candidates = $scheduledShifts->filter(function ($s) use ($now, $filter) {
            if ($filter && !$filter($s)) {
                return false;
            }

            if (empty($s['start_time']) || empty($s['end_time']) || !$s['is_working']) {
                return false;
            }

            $startDate = !empty($s['shift_date']) ? $s['shift_date'] : date('Y-m-d');
            $endDate = !empty($s['end_date']) ? $s['end_date'] : $startDate;
            $start = strtotime($startDate . ' ' . $s['start_time']);
            $end = strtotime($endDate . ' ' . $s['end_time']);

            return $now >= $start && $now < $end;
        });

        if ($candidates->isEmpty()) {
            return null;
        }

        if ($sortByAttendanceDesc) {
            return $candidates->sortByDesc(fn($s) => $s['attendance']->id)->first();
        }

        return $candidates->sortBy('start_time')->first();
    }
}
