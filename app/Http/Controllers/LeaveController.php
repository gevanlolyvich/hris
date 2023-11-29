<?php

namespace App\Http\Controllers;

use App\Exports\LeaveExport;
use App\Models\Employee;
use App\Models\Leave as LocalLeave;
use App\Models\LeaveType;
use App\Mail\LeaveActionSend;
use App\Models\AttendanceEmployee;
use App\Models\AttendanceRequest;
use App\Models\AttendanceStatus;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\GoogleCalendar\Event as GoogleEvent;

class LeaveController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('Manage Leave')) {
            $leaves = LocalLeave::where('created_by', '=', \Auth::user()->creatorId())->get();
            if (\Auth::user()->type == 'employee') {
                $user     = \Auth::user();
                $employee = Employee::where('user_id', '=', $user->id)->first();
                $leaves   = LocalLeave::where('employee_id', '=', $employee->id)->get();
            } else {
                $leaves = LocalLeave::where('created_by', '=', \Auth::user()->creatorId())->get();
            }

            return view('leave.index', compact('leaves'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Leave')) {
            if (Auth::user()->type == 'employee') {
                $employees = Employee::where('user_id', '=', \Auth::user()->id)->orderby('name', 'asc')->get()->pluck('name', 'id');
            } else {
                $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->orderby('name', 'asc')->get()->pluck('name', 'id');
            }
            $leavetypes      = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();
            $leavetypes_days = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();

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
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }


            $employee = Employee::where('user_id', '=', Auth::user()->id)->first();
            $leave_type = LeaveType::find($request->leave_type_id);
            $startDate = new \DateTime($request->start_date);
            $endDate = new \DateTime($request->end_date);
            $total_leave_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;
            // return $total_leave_days;
            if ($leave_type->days >= $total_leave_days) {
                $leave    = new LocalLeave();
                if (\Auth::user()->type == "employee") {
                    $leave->employee_id = $employee->id;
                } else {
                    $leave->employee_id = $request->employee_id;
                }

                $employee = Employee::find($leave->employee_id);
                $document_path = null;
                if ($request->file('document')) {
                    $docs = $request->file('document');
                    $docName = time() . "_" . date('Y-m-d') . "_" . preg_replace('/\s+/', '', $employee->name) . "." . $docs->getClientOriginalExtension();
                    $path = $docs->storeAs('uploads/leaves', $docName, 'public');
                    $document_path = env('APP_URL') . '/storage/' . $path;
                }

                $leave->leave_type_id    = $request->leave_type_id;
                $leave->applied_on       = date('Y-m-d');
                $leave->start_date       = $request->start_date;
                $leave->end_date         = $request->end_date;
                $leave->total_leave_days = $total_leave_days + 1;
                $leave->leave_reason     = $request->leave_reason;
                $leave->remark           = $request->remark;
                $leave->location         = $request->location;
                $leave->document_path    = $document_path;
                $leave->status           = 'Pending';
                $leave->created_by       = \Auth::user()->creatorId();

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

                return redirect()->route('leave.index')->with('success', __('Leave  successfully created.'));
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

        // return $leave;
        if (\Auth::user()->can('Edit Leave')) {
            if ($leave->created_by == \Auth::user()->creatorId()) {
                $employees = null;
                if (Auth::user()->type == 'employee') {
                    $employees = Employee::where('user_id', '=', \Auth::user()->id)->orderby('name', 'asc')->get()->pluck('name', 'id');
                } else {
                    $employees  = Employee::where('created_by', '=', \Auth::user()->creatorId())->orderby('name', 'asc')->get()->pluck('name', 'id');
                }
                $leavetypes = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();

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
            if ($leave->created_by == \Auth::user()->creatorId()) {
                $employees  = Employee::where('created_by', '=', \Auth::user()->creatorId())->orderby('name', 'asc')->get()->pluck('name', 'id');
                $leavetypes = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('title', 'id');

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
            if ($leave->created_by == Auth::user()->created_by) {
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
                $leave_type = LeaveType::find($request->leave_type_id);
                $employee = Employee::find($leave->employee_id);
                $document_path = null;
                if ($request->file('document')) {
                    $docs = $request->file('document');
                    $docName = time() . "_" . date('Y-m-d') . "_" . preg_replace('/\s+/', '', $employee->name) . "." . $docs->getClientOriginalExtension();
                    $path = $docs->storeAs('uploads/leaves', $docName, 'public');
                    $document_path = env('APP_URL') . '/storage/' . $path;
                }

                $startDate = new \DateTime($request->start_date);
                $endDate = new \DateTime($request->end_date);
                $total_leave_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;
                if ($leave_type->days >= $total_leave_days) {
                    $leave->leave_type_id    = $request->leave_type_id;
                    $leave->start_date       = $request->start_date;
                    $leave->end_date         = $request->end_date;
                    $leave->total_leave_days = $total_leave_days + 1;
                    $leave->leave_reason     = $request->leave_reason;
                    $leave->remark           = $request->remark;
                    $leave->location         = $request->location;
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
            if ($leave->created_by == \Auth::user()->creatorId()) {
                $leave->delete();

                return redirect()->route('leave.index')->with('success', __('Leave successfully deleted.'));
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
    
                AttendanceEmployee::create([
                    'employee_id'           => $leave->employee_id,
                    'date'                  => $date,
                    'attendance_status_id'  => $leaveAttendance->id,
                    'status'                => $leaveAttendance->name,
                    'clock_in'              => '00:00:00',
                    'clock_out'             => '00:00:00',
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
                ]);
            }
        }

        // twilio  
        $setting = Utility::settings();
        $emp = Employee::find($leave->employee_id);
        if (isset($setting['twilio_leave_approve_notification']) && $setting['twilio_leave_approve_notification'] == 1) {
            $msg = __("Your leave has been") . ' ' . $leave->status . '.';


            Utility::send_twilio_msg($emp->phone, $msg);
        }

        $setings = Utility::settings();
        if ($setings['leave_status'] == 1) {
            $employee     = Employee::where('id', $leave->employee_id)->where('created_by', '=', \Auth::user()->creatorId())->first();
            $uArr = [
                'leave_status_name' => $employee->name,
                'leave_status' => $request->status,
                'leave_reason' => $leave->leave_reason,
                'leave_start_date' => $leave->start_date,
                'leave_end_date' => $leave->end_date,
                'total_leave_days' => $leave->total_leave_days,


            ];
            $resp = Utility::sendEmailTemplate('leave_status', [$employee->email], $uArr);
            return redirect()->route('leave.index')->with('success', __('Leave status successfully updated.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        }

        return redirect()->route('leave.index')->with('success', __('Leave status successfully updated.'));
    }

    public function jsoncount(Request $request)
    {
        //        $leave_counts = LeaveType::select(\DB::raw('COALESCE(SUM(leaves.total_leave_days),0) AS total_leave, leave_types.title, leave_types.days,leave_types.id'))->leftjoin(
        //            'leaves', function ($join) use ($request){
        //            $join->on('leaves.leave_type_id', '=', 'leave_types.id');
        //            $join->where('leaves.employee_id', '=', $request->employee_id);
        //        }
        //        )->groupBy('leaves.leave_type_id')->get();

        $leave_counts = LeaveType::select(\DB::raw('COALESCE(SUM(leaves.total_leave_days),0) AS total_leave, leave_types.title, leave_types.days,leave_types.id'))
            ->leftjoin(
                'leaves',
                function ($join) use ($request) {
                    $join->on('leaves.leave_type_id', '=', 'leave_types.id');
                    $join->where('leaves.employee_id', '=', $request->employee_id);
                }
            )->groupBy('leaves.leave_type_id')->get();

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

        $leaves = LocalLeave::where('created_by', '=', \Auth::user()->creatorId())->get();
        if (\Auth::user()->type == 'employee') {
            $user     = \Auth::user();
            $employee = Employee::where('user_id', '=', $user->id)->first();
            $leaves   = LocalLeave::where('employee_id', '=', $employee->id)->get();
        } else {
            $leaves = LocalLeave::where('created_by', '=', \Auth::user()->creatorId())->get();
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
