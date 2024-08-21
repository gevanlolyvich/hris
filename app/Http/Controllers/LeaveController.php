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
use App\Models\Utility;
use App\Models\PushSubscription;
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
                $user     = \Auth::user();
                
                $subordinate_ids = \Auth::user()?->employee?->subordinatesFlatten()->pluck('id')->toArray();
                $employee_id = null;
                if (!empty($subordinate_ids))
                {
                    $employee_id = $subordinate_ids;
                    $employee_id[] = \Auth::user()->employee->id;
                }
                else 
                {
                    $employee_id[] = \Auth::user()->employee->id;
                }

                $leaves   = LocalLeave::whereIn('employee_id', $employee_id);
            } else {
                $leaves = $branch_id?->isNotEmpty() ? LocalLeave::whereHas('employees', function ($query) use ($branch_id) { $query->whereIn('branch_id', $branch_id); })->orderBy('start_date', 'DESC') : LocalLeave::orderBy('start_date', 'DESC');
            }

            if ($status != null && $status == 'Pending') {
                $leaves->where('status', 'Pending');
            }

            if (!empty($request->branch_id)) {
                $department     = Department::where('branch_id', $request->branch_id)->get()->pluck('name', 'id');
                $leaves         = $leaves->whereHas('employees', function ($query) use ($request) { $query->where('branch_id', $request->branch_id); });
            }
            if (!empty($request->department_id)) {
                $department     = empty($request->branch_id) ? Department::where('department_id', $request->department_id)->get()->pluck('name', 'id') : $department;
                $leaves         = $leaves->whereHas('employees', function ($query) use ($request) { $query->where('department_id', $request->department_id); });
            }

            $leaves = $leaves->orderBy('start_date', 'DESC')->get();

            $branch_count = 2;
            foreach ($branch as $index => $b) {
                if ($b == 'Head Office') {
                    $branch[$index] = '1. '.  $b;
                } else {
                    $branch[$index] = $branch_count. '. ' . __($b);
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
            $leavetypes      = LeaveType::get();
            $leavetypes_days = LeaveType::get();

            return view('leave.create', compact('employees', 'leavetypes', 'leavetypes_days'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Leave')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'leave_type_id' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required',
                    'leave_reason' => 'required',
                    'remark' => 'required',
                    'location' => 'required',
                    'myDocument' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }


            $employee = Employee::where('is_active', 1)->where('user_id', '=', Auth::user()->id)->first();

            $leave_type = LeaveType::find($request->leave_type_id);
            $startDate = new \DateTime($request->start_date);
            $endDate = new \DateTime($request->end_date);
            $start_date = date($request->start_date);
            $end_date   = date($request->end_date);
            $total_leave_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 1;
            $total_leave_days += 1;
            // return $total_leave_days;
            if ($leave_type->days >= $total_leave_days) {
                $leave    = new LocalLeave();
                if (\Auth::user()->type == "employee") {
                    $leave->employee_id = $employee->id;
                } else {
                    $leave->employee_id = $request->employee_id;
                }

                $employee = Employee::where('is_active', 1)->find($leave->employee_id);

                if (empty($employee) || !$employee) {
                    return redirect()->back()->with('error', __('Inactive'));
                }

                $duplicate_leave = LocalLeave::where('employee_id', $leave->employee_id)
                ->where(function ($query) use ($start_date, $end_date) {
                    $query->whereBetween('start_date', [$start_date, $end_date])
                        ->orWhereBetween('end_date', [$start_date, $end_date])
                        ->orWhere(function ($query) use ($start_date, $end_date) {
                            $query->where('start_date', '<=', $start_date)
                                    ->where('end_date', '>=', $end_date);
                        });
                })
                ->first();

                if (!empty($duplicate_leave)) {
                    return redirect()->back()->with('error', __('Leave Already Exist In That Date Range'));
                }
        
                $document_path = null;
                if ($request->file('myDocument')) {
                    $docs = $request->file('myDocument');
                    $docName = time() . "_" . date('Y-m-d') . "_" . preg_replace('/\s+/', '', $employee->name) . "." . $docs->getClientOriginalExtension();
                    $path = $docs->storeAs('uploads/leaves', $docName, 'public');
                    $document_path = env('APP_URL') . '/storage/' . $path;
                }

                $leave->leave_type_id    = $request->leave_type_id;
                $leave->applied_on       = date('Y-m-d');
                $leave->start_date       = $request->start_date;
                $leave->end_date         = $request->end_date;
                $leave->total_leave_days = $total_leave_days;
                $leave->leave_reason     = $request->leave_reason;
                $leave->remark           = $request->remark;
                $leave->location         = $request->location;
                $leave->document_path    = $document_path;
                $leave->status           = 'Pending';
                $leave->created_by       = \Auth::user()->id;

                $leave->save();

                // Google celander
                if ($request->get('synchronize_type')  == 'google_calender') {

                    $type = 'leave';
                    $request1 = new GoogleEvent();
                    $request1->title = !empty(\Auth::user()->getLeaveType($leave->leave_type_id)) ? \Auth::user()->getLeaveType($leave->leave_type_id)->title : '';
                    $request1->start_date = $request->start_date;
                    $request1->end_date = $request->end_date;

                    Utility::addCalendarData($request1, $type);
                }

                // Send Notification To HR
                $subscriptions = [];
                $pushSubscriptions = PushSubscription::whereHas('user', function ($query) use ($employee) {
                    $query->where('type', 'hr')
                        ->where(function ($query) use ($employee) {
                            $query->whereNull('branch_id')
                                    ->orWhere('branch_id', $employee->branch_id);
                        });
                })->get();
                foreach ($pushSubscriptions as $sub) {
                    array_push($subscriptions, ['data' => $sub->data, 'name' => $sub->user->name]);
                }
                \Auth::user()->sendNotifications(
                    $subscriptions,
                    json_encode([
                        'title' => __('New Leave Request'),
                        'body' => $employee->name . ' ' . __('Make Leave Request') . ' [' . $leave->leaveType->title . '] ' . __('For Date') . ' ' . $request->start_date . ' ' . __('To Date') . ' ' . $request->end_date,
                        'url' => "/leave"
                    ]),
                    'high'
                );

                return redirect()->back()->with('success', __('Leave  successfully created.'));
            } else {
                return redirect()->back()->with('error', __('Leave type ' . $leave_type->name . ' is provide maximum ' . $leave_type->days . "  days please make sure your selected days is under " . $leave_type->days . ' days.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
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
                    $type->title    = '( ' . $type->total_leave . ' / ' . $type->days . ' ) | ' . $type->title;
                }
                
                $leavetypes         = $leavetypes->pluck('title', 'id');

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

    public function update(Request $request, $leave)
    {
        $leave = LocalLeave::find($leave);
        if (\Auth::user()->can('Edit Leave')) {
            if (($leave->created_by == Auth::user()->id || $leave->employee_id == Auth::user()->employee->id || Auth::user()->type != 'employee') && $leave->status != "Approved") {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'leave_type_id' => 'required',
                        'start_date' => 'required',
                        'end_date' => 'required',
                        'leave_reason' => 'required',
                        'remark' => 'required',
                        'location'  => 'required'
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }
                $start_date = date($request->start_date);
                $end_date   = date($request->end_date);
                $leave_type = LeaveType::find($request->leave_type_id);
                $employee = Employee::where('is_active', 1)->find($leave->employee_id);

                if (empty($employee) || !$employee) {
                    return redirect()->back()->with('error', __('Inactive'));
                }

                $duplicate_leave = LocalLeave::whereNot('id', $leave->id)->where('employee_id', $leave->employee_id)
                    ->where(function ($query) use ($start_date, $end_date) {
                        $query->whereBetween('start_date', [$start_date, $end_date])
                            ->orWhereBetween('end_date', [$start_date, $end_date])
                            ->orWhere(function ($query) use ($start_date, $end_date) {
                                $query->where('start_date', '<=', $start_date)
                                        ->where('end_date', '>=', $end_date);
                            });
                    })
                    ->first();

                if (!empty($duplicate_leave)) {
                    return redirect()->back()->with('error', __('Leave Already Exist In That Date Range'));
                }

                $leaves_same_type = LocalLeave::whereNot('id', $leave->id)->where('employee_id', $leave->employee_id)->where('leave_type_id', $leave->leave_type_id)->get();
                $total_days = $leaves_same_type->sum(function ($leaveData) {
                    return (float) $leaveData->total_leave_days;
                });
                
                $startDate = new \DateTime($request->start_date);
                $endDate = new \DateTime($request->end_date);
                $total_leave_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 1;
                $total_leave_days += 1;
                if ($total_days <= $leave_type->days && ($total_days + $total_leave_days + 1) <= $leave_type->days) {
                    $document_path = null;
                    if ($request->file('myDocument')) {
                        $docs = $request->file('myDocument');
                        $docName = time() . "_" . date('Y-m-d') . "_" . preg_replace('/\s+/', '', $employee->name) . "." . $docs->getClientOriginalExtension();
                        $path = $docs->storeAs('uploads/leaves', $docName, 'public');
                        $document_path = env('APP_URL') . '/storage/' . $path;
                    }

                    $leave->leave_type_id    = $request->leave_type_id;
                    $leave->start_date       = $request->start_date;
                    $leave->end_date         = $request->end_date;
                    $leave->total_leave_days = $total_leave_days;
                    $leave->leave_reason     = $request->leave_reason;
                    $leave->remark           = $request->remark;
                    $leave->location         = $request->location;
                    $leave->status           = 'Pending';
                    $leave->document_path    = $document_path ? $document_path : $leave->document_path;

                    $leave->save();

                    return redirect()->route('leave.index')->with('success', __('Leave successfully updated.'));
                } else {
                    return redirect()->back()->with('error', __('Leave type ' . $leave_type->name . ' is provide maximum ' . $leave_type->days . "  days please make sure your selected days is under " . $leave_type->days . ' days.'));
                }
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
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
        $leave     = LocalLeave::find($id);
        $employee  = Employee::find($leave->employee_id);
        $leavetype = LeaveType::find($leave->leave_type_id);

        return view('leave.action', compact('employee', 'leavetype', 'leave'));
    }

    public function changeaction(Request $request)
    {
        // return $request;
        $dates = [];
        $leave = LocalLeave::find($request->leave_id);
        $leaveType = LeaveType::find($leave?->leave_type_id);
        if (empty($leaveType) || !$leaveType?->is_active) {
            return redirect()->back()->with('error', __('Leave Type Is Inactive'));
        }

        $leave->status = $request->status;
        $leave->note = $request->note;
        if ($leave->status == 'Approved') {
            $startDate               = new \DateTime($leave->start_date);
            $endDate                 = new \DateTime($leave->end_date);
            $total_leave_days        = $startDate->diff($endDate)->days;
            $leave->total_leave_days = $total_leave_days + 1;
            $leave->status           = 'Approved';
            $leave->note             = $request->note;
        }

        $leave->save();

        if ($leave->start_date == $leave->end_date) {
            array_push($dates, $leave->start_date);
        } else {
            $period = new \DatePeriod(
                new \DateTime($leave->start_date),
                new \DateInterval('P1D'),
                new \DateTime(date('Y-m-d', strtotime('+1 day', strtotime($leave->end_date))))
            );

            foreach ($period as $key => $value) {
                array_push($dates, $value->format('Y-m-d'));
            }
        }

        if ($request->status == 'Approved') {
            $leaveAttendance = AttendanceStatus::find(4);
            for ($i = 0; $i < count($dates); $i++) {
                $date = $dates[$i];
    
                AttendanceEmployee::where('employee_id', $leave->employee_id)->where('date', $date)->delete();
                AttendanceEmployee::create([
                    'employee_id'           => $leave->employee_id,
                    'date'                  => $date,
                    'attendance_status_id'  => $leaveAttendance->id,
                    'status'                => $leaveAttendance->name,
                    'clock_in'              => '00:00:00',
                    'clock_out'             => '00:00:01',
                    'late'                  => '00:00:00',
                    'early_leaving'         => '00:00:00',
                    'work_hours'            => '00:00:00',
                    'overtime'              => '00:00:00',
                    'total_rest'            => '00:00:00',
                    'created_by'            => $leave->employee_id,
                    'attendance_type_id'    => null, //* ON SITE
                    'coord_in'              => null,
                    'coord_out'             => null,
                    'is_valid'              => true,
                    'validate_by'           => Auth::user()->id,
                    'shift_type_id'         => $leave->employees->shift_type_id,
                    'source_in'             => 'Application',
                    'source_out'            => 'Application'
                ]);
            }
        }

        // twilio  
        $setting = Utility::settings();
        $emp = Employee::where('is_active', 1)->find($leave->employee_id);

        if (empty($emp) || !$emp) {
            return redirect()->back()->with('error', __('Inactive'));
        }

        if (isset($setting['twilio_leave_approve_notification']) && $setting['twilio_leave_approve_notification'] == 1) {
            $msg = __("Your leave has been") . ' ' . $leave->status . '.';


            Utility::send_twilio_msg($emp->phone, $msg);
        }

        $setings = Utility::settings();
        if ($setings['leave_status'] == 1) {
            $employee     = Employee::where('is_active', 1)->where('id', $leave->employee_id)->first();

            if (empty($employee) || !$employee) {
                return redirect()->back()->with('error', __('Inactive'));
            }

            $uArr = [
                'leave_status_name' => $employee->name,
                'leave_status' => $request->status,
                'leave_reason' => $leave->leave_reason,
                'leave_start_date' => $leave->start_date,
                'leave_end_date' => $leave->end_date,
                'total_leave_days' => $leave->total_leave_days,


            ];
            $resp = Utility::sendEmailTemplate('leave_status', [$employee->email], $uArr);

            // Send push notification to requester
            $subscriptions = [];
            if ($leave->employees?->user?->pushNotifications) {
                foreach ($leave->employees->user->pushNotifications ?? [] as $sub) {
                    array_push($subscriptions, ['data' => $sub->data, 'name' => $leave->employees->name]);
                }
            }
            $status = $request->status == 'Approved' ? 'Approved' : 'Rejected';
            \Auth::user()->sendNotifications(
                $subscriptions,
                json_encode([
                    'title' => __('Leave') . ' ' . __($status),
                    'body' => __('Leave Request') . ' [' . $leave->leaveType->title . '] ' . __('For Date') . ' ' . $leave->start_date . ' ' . __('To Date') . ' ' . $leave->end_date . ' ' . __($status),
                    'url' => "/leave"
                ]),
                'high'
            );
            return redirect()->back()->with('success', __('Leave status successfully updated.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        }

        return redirect()->back()->with('success', __('Leave status successfully updated.'));
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
            $arr['id']        = $meeting['id'];
            $arr['employee_id']     = $meeting['employee_id'];
            // $arr['leave_type_id']     = date('Y-m-d', strtotime($meeting['start_date']));
        }

        $leaves = LocalLeave::get();
        if (\Auth::user()->type == 'employee') {
            $user     = \Auth::user();
            $employee = Employee::where('user_id', '=', $user->id)->first();
            $leaves   = LocalLeave::where('employee_id', '=', $employee->id)->get();
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
            $arrayJson =  Utility::getCalendarData($type);
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
