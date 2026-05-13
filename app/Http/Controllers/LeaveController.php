<?php

namespace App\Http\Controllers;

use App\Exports\LeaveExport;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Leave as LocalLeave;
use App\Models\LeaveType;
use App\Mail\LeaveActionSend;
use App\Models\AttendanceEmployee;
use App\Models\AttendanceRequest;
use App\Models\AttendanceStatus;
use App\Models\Holiday;
use App\Models\Utility;
use App\Models\PushSubscription;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\GoogleCalendar\Event as GoogleEvent;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        if (\Auth::user()->can('Manage Leave')) {
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

            $status = $request->query('status', null);
            $branch = $branch_id?->isNotEmpty() ? Branch::whereIn('id', $branch_id)->get()->pluck('name', 'id') : Branch::get()->pluck('name', 'id');
            $department = collect();

            if (\Auth::user()->type == 'employee') {
                $user = \Auth::user();

                $subordinate_ids = \Auth::user()?->employee?->subordinatesFlatten()->pluck('id')->toArray();
                $employee_id = null;
                if (!empty($subordinate_ids)) {
                    $employee_id = $subordinate_ids;
                    $employee_id[] = \Auth::user()->employee->id;
                } else {
                    $employee_id[] = \Auth::user()->employee->id;
                }

                $leaves = LocalLeave::whereIn('employee_id', $employee_id);
            } else {
                $leaves = $branch_id?->isNotEmpty() ? LocalLeave::whereHas('employees', function ($query) use ($branch_id) {
                    $query->whereIn('branch_id', $branch_id);
                })->orderBy('start_date', 'DESC') : LocalLeave::orderBy('start_date', 'DESC');
            }

            if ($status != null && $status == 'Pending') {
                $leaves->where('status', 'Pending');
            }

            if (!empty($request->branch_id)) {
                $department = Department::where('branch_id', $request->branch_id)->get()->pluck('name', 'id');
                $leaves = $leaves->whereHas('employees', function ($query) use ($request) {
                    $query->where('branch_id', $request->branch_id);
                });
            }
            if (!empty($request->department_id)) {
                $department = empty($request->branch_id) ? Department::where('department_id', $request->department_id)->get()->pluck('name', 'id') : $department;
                $leaves = $leaves->whereHas('employees', function ($query) use ($request) {
                    $query->where('department_id', $request->department_id);
                });
            }

            $leaves = $leaves->orderBy('start_date', 'DESC')->get();

            $branch_count = 2;
            foreach ($branch as $index => $b) {
                if ($b == 'Head Office') {
                    $branch[$index] = '1. ' . $b;
                } else {
                    $branch[$index] = $branch_count . '. ' . __($b);
                    $branch_count += 1;
                }
            }

            return view('leave.index', compact('leaves', 'branch', 'department'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Leave')) {
            if (Auth::user()->type == 'employee') {
                $employees = Employee::where('is_active', 1)->where('user_id', '=', \Auth::user()->id)->orderby('name', 'asc')->get()->pluck('name', 'id');
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

                $employees = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id') : Employee::where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id');
            }
            $leavetypes = LeaveType::get();
            $leavetypes_days = LeaveType::get();

            return view('leave.create', compact('employees', 'leavetypes', 'leavetypes_days'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('Create Leave')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'leave_type_id' => 'required',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'leave_reason' => 'required',
                'remark' => 'required',
                'location' => 'required',
                'myDocument' => 'required|file',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        if ($request->start_date > $request->end_date) {
            return redirect()->back()->with('error', 'Start date tidak boleh lebih besar dari end date');
        }

        if (\Auth::user()->type == "employee") {
            $employee = Employee::with('shift_type.shiftTimes')
                ->where('user_id', \Auth::user()->id)
                ->first();
        } else {
            $employee = Employee::with('shift_type.shiftTimes')
                ->find($request->employee_id);
        }

        if (!$employee || !$employee->shift_type) {
            return redirect()->back()->with('error', 'Shift belum di assign ke employee');
        }

        $leave_type = LeaveType::find($request->leave_type_id);
        if (!$leave_type) {
            return redirect()->back()->with('error', 'Leave type tidak ditemukan');
        }

        $holidays = Holiday::where(function ($q) use ($request) {
            $q->where('start_date', '<=', $request->end_date)
                ->where('end_date', '>=', $request->start_date);
        })->get();

        $period = CarbonPeriod::create($request->start_date, $request->end_date);
        $total_leave_days = 0;

        // Loop through each date in the period
        foreach ($period as $date) {

            $dayName = strtolower($date->format('l'));

            $isHoliday = $holidays->first(function ($h) use ($date) {
                return $date->between(
                    \Carbon\Carbon::parse($h->start_date),
                    \Carbon\Carbon::parse($h->end_date)
                );
            });

            if ($isHoliday)
                continue;

            $shift = \DB::table('shift_times')
                ->where('shift_type_id', $employee->shift_type_id)
                ->whereRaw('LOWER(days) = ?', [$dayName])
                ->where('is_working', 1)
                ->first();

            if ($shift) {
                $total_leave_days++;
            }
        }

        if ($leave_type->days < $total_leave_days) {
            return redirect()->back()->with(
                'error',
                __('Leave type ' . $leave_type->name . ' maksimum ' . $leave_type->days . ' hari.')
            );
        }

        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $duplicate_leave = LocalLeave::where('employee_id', $employee->id)
            ->where(function ($query) use ($start_date, $end_date) {
                $query->whereBetween('start_date', [$start_date, $end_date])
                    ->orWhereBetween('end_date', [$start_date, $end_date])
                    ->orWhere(function ($q) use ($start_date, $end_date) {
                        $q->where('start_date', '<=', $start_date)
                            ->where('end_date', '>=', $end_date);
                    });
            })
            ->first();

        if ($duplicate_leave) {
            return redirect()->back()->with('error', __('Leave sudah ada di range tanggal tersebut'));
        }

        $employeeActive = Employee::where('is_active', 1)->find($employee->id);
        if (!$employeeActive) {
            return redirect()->back()->with('error', __('Employee inactive'));
        }

        $document_path = null;
        if ($request->file('myDocument')) {
            $docs = $request->file('myDocument');

            $docName = time() . "_" . date('Y-m-d') . "_" .
                preg_replace('/\s+/', '', $employee->name) . "." .
                $docs->getClientOriginalExtension();

            $path = $docs->storeAs('uploads/leaves', $docName, 'public');
            $document_path = env('APP_URL') . '/storage/' . $path;
        }

        $leave = new LocalLeave();

        $leave->employee_id = $employee->id;
        $leave->leave_type_id = $request->leave_type_id;
        $leave->applied_on = date('Y-m-d');
        $leave->start_date = $start_date;
        $leave->end_date = $end_date;
        $leave->total_leave_days = $total_leave_days;
        $leave->leave_reason = $request->leave_reason;
        $leave->remark = $request->remark;
        $leave->location = $request->location;
        $leave->document_path = $document_path;
        $leave->status = 'Pending';
        $leave->created_by = \Auth::user()->id;

        $leave->save();

        // sync to google calendar
        if ($request->get('synchronize_type') == 'google_calender') {

            $type = 'leave';

            $request1 = new GoogleEvent();
            $request1->title = optional(\Auth::user()->getLeaveType($leave->leave_type_id))->title;
            $request1->start_date = $start_date;
            $request1->end_date = $end_date;

            Utility::addCalendarData($request1, $type);
        }

        // Send push notification to HR
        $subscriptions = [];

        $pushSubscriptions = PushSubscription::whereHas('user', function ($query) use ($employee) {
            $query->where('type', 'hr')
                ->where(function ($q) use ($employee) {
                    $q->whereNull('branch_id')
                        ->orWhere('branch_id', $employee->branch_id);
                });
        })->get();

        foreach ($pushSubscriptions as $sub) {
            $subscriptions[] = [
                'data' => $sub->data,
                'name' => $sub->user->name
            ];
        }

        \Auth::user()->sendNotifications(
            $subscriptions,
            json_encode([
                'title' => __('New Leave Request'),
                'body' => $employee->name . ' request leave [' . optional($leave->leaveType)->title . '] ' . $start_date . ' - ' . $end_date,
                'url' => "/leave"
            ]),
            'high'
        );

        return redirect()->back()->with('success', __('Leave successfully created.'));
    }

    public function show(LocalLeave $leave)
    {
        return redirect()->route('leave.index');
    }

    public function edit(LocalLeave $leave)
    {
        if (\Auth::user()->can('Edit Leave')) {
            if (($leave->created_by == Auth::user()->id || $leave->employee_id == Auth::user()->employee->id || Auth::user()->type != 'employee') && $leave->status != "Approved") {
                $employees = null;
                if (Auth::user()->type == 'employee') {
                    $employees = Employee::where('is_active', 1)->where('user_id', '=', \Auth::user()->id)->orderby('name', 'asc')->get()->pluck('name', 'id');
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

                    $employees = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id') : Employee::where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id');
                }
                $leavetypes = LeaveType::select(\DB::raw('COALESCE(SUM(leaves.total_leave_days), 0) AS total_leave, leave_types.title, leave_types.days, leave_types.id'))
                    ->leftJoin('leaves', function ($join) use ($leave) {
                        $join->on('leaves.leave_type_id', '=', 'leave_types.id');
                        $join->where('leaves.employee_id', '=', $leave->employee_id);
                    })
                    ->where('leave_types.is_active', 1)
                    ->groupBy('leave_types.id', 'leave_types.title', 'leave_types.days')
                    ->get();

                foreach ($leavetypes as $type) {
                    $type->title = '( ' . $type->total_leave . ' / ' . $type->days . ' ) | ' . $type->title;
                }

                $leavetypes = $leavetypes->pluck('title', 'id');

                return view('leave.edit', compact('leave', 'employees', 'leavetypes'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function editA(AttendanceRequest $leave)
    {

        return $leave;
        if (\Auth::user()->can('Edit Leave')) {
            if ($leave->created_by == \Auth::user()->id || \Auth::user()->type != 'company') {
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

                $employees = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id') : Employee::where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id');
                $leavetypes = LeaveType::get()->pluck('title', 'id');

                return view('leave.edit', compact('leave', 'employees', 'leavetypes'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, $leaveId)
    {
        $leave = LocalLeave::find($leaveId);

        if (!\Auth::user()->can('Edit Leave')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if (
            !(
                $leave->created_by == \Auth::user()->id ||
                $leave->employee_id == \Auth::user()->employee->id ||
                \Auth::user()->type != 'employee'
            ) || $leave->status == "Approved"
        ) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'leave_type_id' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'leave_reason' => 'required',
            'remark' => 'required',
            'location' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        if ($request->start_date > $request->end_date) {
            return redirect()->back()->with('error', 'Start date tidak boleh lebih besar dari end date');
        }

        $leave_type = LeaveType::find($request->leave_type_id);
        $employee = Employee::with('shift_type')->where('is_active', 1)->find($leave->employee_id);

        if (!$employee || !$employee->shift_type) {
            return redirect()->back()->with('error', __('Employee inactive / shift belum di assign'));
        }

        $start_date = $request->start_date;
        $end_date = $request->end_date;

        // Cek duplicate
        $duplicate_leave = LocalLeave::where('employee_id', $employee->id)
            ->where('id', '!=', $leave->id)
            ->where(function ($query) use ($start_date, $end_date) {
                $query->whereBetween('start_date', [$start_date, $end_date])
                    ->orWhereBetween('end_date', [$start_date, $end_date])
                    ->orWhere(function ($q) use ($start_date, $end_date) {
                        $q->where('start_date', '<=', $start_date)
                            ->where('end_date', '>=', $end_date);
                    });
            })
            ->first();

        if ($duplicate_leave) {
            return redirect()->back()->with('error', __('Leave sudah ada di range tanggal tersebut'));
        }

        // holidays
        $holidays = Holiday::where(function ($q) use ($request) {
            $q->where('start_date', '<=', $request->end_date)
                ->where('end_date', '>=', $request->start_date);
        })->get();

        // Shift times by day name
        $shiftTimes = \DB::table('shift_times')
            ->where('shift_type_id', $employee->shift_type_id)
            ->get()
            ->keyBy(function ($item) {
                return strtolower($item->days);
            });

        // Loop through each date in the period and count leave days
        $period = \Carbon\CarbonPeriod::create($start_date, $end_date);
        $total_leave_days = 0;

        foreach ($period as $date) {

            $dayName = strtolower($date->format('l'));

            $isHoliday = $holidays->first(function ($h) use ($date) {
                return $date->between(
                    \Carbon\Carbon::parse($h->start_date),
                    \Carbon\Carbon::parse($h->end_date)
                );
            });

            if ($isHoliday)
                continue;

            $shift = $shiftTimes[$dayName] ?? null;

            if ($shift && $shift->is_working == 1) {
                $total_leave_days++;
            }
        }

        // Cek total leave days dengan leave type
        $leaves_same_type = LocalLeave::where('employee_id', $employee->id)
            ->where('leave_type_id', $request->leave_type_id)
            ->where('id', '!=', $leave->id)
            ->get();

        $used_days = $leaves_same_type->sum('total_leave_days');

        if (($used_days + $total_leave_days) > $leave_type->days) {
            return redirect()->back()->with(
                'error',
                __('Leave type ' . $leave_type->name . ' maksimum ' . $leave_type->days . ' hari.')
            );
        }

        // Handle document upload
        $document_path = $leave->document_path;
        if ($request->file('myDocument')) {
            $docs = $request->file('myDocument');

            $docName = time() . "_" . date('Y-m-d') . "_" .
                preg_replace('/\s+/', '', $employee->name) . "." .
                $docs->getClientOriginalExtension();

            $path = $docs->storeAs('uploads/leaves', $docName, 'public');
            $document_path = env('APP_URL') . '/storage/' . $path;
        }

        // Update leave data
        $leave->leave_type_id = $request->leave_type_id;
        $leave->start_date = $start_date;
        $leave->end_date = $end_date;
        $leave->total_leave_days = $total_leave_days;
        $leave->leave_reason = $request->leave_reason;
        $leave->remark = $request->remark;
        $leave->location = $request->location;
        $leave->status = 'Pending';
        $leave->document_path = $document_path;

        $leave->save();

        return redirect()->route('leave.index')->with('success', __('Leave successfully updated.'));
    }


    public function destroy(LocalLeave $leave)
    {
        if (\Auth::user()->can('Delete Leave')) {
            if ((($leave->created_by == Auth::user()->id || $leave->employee_id == Auth::user()?->employee?->id) && $leave->status != "Approved") || Auth::user()->type != 'employee') {

                if ($leave->document_path) {
                    $filepath_array = explode('/', $leave->document_path);
                    $filename = array_pop($filepath_array);

                    // Check if the file exists before attempting to delete
                    if (Storage::disk('public')->exists("uploads/leaves/$filename")) {
                        Storage::disk('public')->delete("uploads/leaves/$filename");
                    }
                }

                if ($leave->status == "Approved") {
                    $dates = [];
                    $period = new \DatePeriod(
                        new \DateTime($leave->start_date),
                        new \DateInterval('P1D'),
                        new \DateTime(date('Y-m-d', strtotime('+1 day', strtotime($leave->end_date))))
                    );

                    foreach ($period as $key => $value) {
                        array_push($dates, $value->format('Y-m-d'));
                    }

                    AttendanceEmployee::where('employee_id', $leave->employee_id)->whereIn('date', $dates)->where('status', 'Leave')->delete();
                }

                $leave->delete();

                return redirect()->back()->with('success', __('Leave successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function action($id)
    {
        $leave = LocalLeave::find($id);
        $employee = Employee::find($leave->employee_id);
        $leavetype = LeaveType::find($leave->leave_type_id);

        return view('leave.action', compact('employee', 'leavetype', 'leave'));
    }

    public function changeaction(Request $request)
    {
        $leave = LocalLeave::with('employees')->find($request->leave_id);

        if (!$leave) {
            return redirect()->back()->with('error', 'Leave tidak ditemukan');
        }

        $leaveType = LeaveType::find($leave->leave_type_id);
        if (!$leaveType || !$leaveType->is_active) {
            return redirect()->back()->with('error', __('Leave Type inactive'));
        }

        $user = Auth::user();
        $subordinates = $user->employee?->subordinatesFlatten()->pluck('id')->toArray() ?? [];
        $isAtasan = in_array($leave->employee_id, $subordinates);

        if (
            !$isAtasan &&
            !($user->type == 'employee' && $user->employee->id == $leave->employee_id)
        ) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $leave->note = $request->note;

        if ($request->status == 'Approved') {

            if (empty($request->selected_dates)) {
                return redirect()->back()->with('error', 'Pilih minimal 1 tanggal!');
            }

            $selectedDates = $request->selected_dates;

            AttendanceEmployee::where('employee_id', $leave->employee_id)
                ->whereBetween('date', [$leave->start_date, $leave->end_date])
                ->where('source_in', 'Application')
                ->delete();

            $leaveAttendance = AttendanceStatus::find(4);

            $period = CarbonPeriod::create($leave->start_date, $leave->end_date);
            $originalDates = [];

            foreach ($period as $date) {
                $day = strtolower($date->format('l'));

                $shift = \DB::table('shift_times')
                    ->where('shift_type_id', $leave->employees->shift_type_id)
                    ->whereRaw('LOWER(days) = ?', [$day])
                    ->where('is_working', 1)
                    ->first();

                if ($shift) {
                    $originalDates[] = $date->format('Y-m-d');
                }
            }

            sort($originalDates);
            sort($selectedDates);

            $isChanged = $originalDates != $selectedDates;

            foreach ($selectedDates as $date) {
                AttendanceEmployee::create([
                    'employee_id' => $leave->employee_id,
                    'date' => $date,
                    'attendance_status_id' => $leaveAttendance->id,
                    'status' => $leaveAttendance->name,
                    'clock_in' => '00:00:00',
                    'clock_out' => '00:00:01',
                    'late' => '00:00:00',
                    'early_leaving' => '00:00:00',
                    'work_hours' => '00:00:00',
                    'overtime' => '00:00:00',
                    'total_rest' => '00:00:00',
                    'created_by' => $leave->employee_id,
                    'attendance_type_id' => null,
                    'coord_in' => null,
                    'coord_out' => null,
                    'is_valid' => $isChanged ? false : true,
                    'validate_by' => $isChanged ? null : Auth::user()->id,
                    'shift_type_id' => $leave->employees?->shift_type_id,
                    'source_in' => 'Application',
                    'source_out' => 'Application'
                ]);
            }

            $leave->total_leave_days = count($selectedDates);

            if ($isChanged) {
                $leave->status = 'Waiting Confirmation';
            } else {
                $leave->status = 'Approved';
            }
        } elseif ($request->status == 'Reject') {
            $leave->status = 'Reject';
        } elseif ($request->status == 'Confirmed') {
            AttendanceEmployee::where('employee_id', $leave->employee_id)
                ->whereBetween('date', [$leave->start_date, $leave->end_date])
                ->where('source_in', 'Application')
                ->update([
                    'is_valid' => true,
                    'validate_by' => Auth::user()->id
                ]);

            $total = AttendanceEmployee::where('employee_id', $leave->employee_id)
                ->whereBetween('date', [$leave->start_date, $leave->end_date])
                ->where('source_in', 'Application')
                ->where('status', 'Leave')
                ->where('is_valid', true)
                ->count();

            $leave->total_leave_days = $total;
            $leave->status = 'Confirmed';
        } elseif ($request->status == 'Cancel') {
            $leave->status = 'Cancel';
        }

        $leave->save();

        return redirect()->back()->with('success', __('Leave status updated.'));
    }

    public function jsoncount(Request $request)
    {
        $leave_counts = LeaveType::select(\DB::raw('COALESCE(SUM(leaves.total_leave_days), 0) AS total_leave, leave_types.id, leave_types.title, leave_types.days'))
            ->leftJoin('leaves', function ($join) use ($request) {
                $join->on('leaves.leave_type_id', '=', 'leave_types.id');
                $join->where('leaves.employee_id', '=', $request->employee_id);
            })
            ->where('leave_types.is_active', 1)
            ->groupBy('leave_types.id', 'leave_types.title', 'leave_types.days')
            ->orderBy('leave_types.title', 'ASC')
            ->get();

        return $leave_counts;
    }
    public function export(Request $request)
    {
        $name = 'Leave' . date('Y-m-d i:h:s');
        $data = Excel::download(new LeaveExport(), $name . '.xlsx');

        return $data;
    }

    public function calender(Request $request)
    {
        $created_by = Auth::user()->created_by;
        $Meetings = LocalLeave::where('created_by', $created_by)->get();
        // dd($Meetings);
        $today_date = date('m');
        $current_month_event = LocalLeave::select('id', 'start_date', 'employee_id', 'created_at')->whereRaw('MONTH(start_date)=' . $today_date)->get();

        $arrMeeting = [];

        foreach ($Meetings as $meeting) {
            // dd($meeting);
            $arr['id'] = $meeting['id'];
            $arr['employee_id'] = $meeting['employee_id'];
            // $arr['leave_type_id']     = date('Y-m-d', strtotime($meeting['start_date']));
        }

        $leaves = LocalLeave::get();
        if (\Auth::user()->type == 'employee') {
            $user = \Auth::user();
            $employee = Employee::where('user_id', '=', $user->id)->first();
            $leaves = LocalLeave::where('employee_id', '=', $employee->id)->get();
        } else {
            $leaves = LocalLeave::get();
        }

        return view('leave.calender', compact('leaves'));
    }

    public function get_leave_data(Request $request)
    {
        $arrayJson = [];
        if ($request->get('calender_type') == 'google_calender') {
            $type = 'leave';
            $arrayJson = Utility::getCalendarData($type);
            // dd($type,$arrayJson);
        } else {
            $data = LocalLeave::get();

            foreach ($data as $val) {
                $end_date = date_create($val->end_date);
                date_add($end_date, date_interval_create_from_date_string("1 days"));
                $arrayJson[] = [
                    "id" => $val->id,
                    "title" => !empty(\Auth::user()->getLeaveType($val->leave_type_id)) ? \Auth::user()->getLeaveType($val->leave_type_id)->title : '',
                    "start" => $val->start_date,
                    "end" => date_format($end_date, "Y-m-d H:i:s"),
                    "className" => $val->color,
                    "textColor" => '#FFF',
                    "allDay" => true,
                    "url" => route('leave.action', $val['id']),
                ];
            }
        }

        return $arrayJson;
    }
}
