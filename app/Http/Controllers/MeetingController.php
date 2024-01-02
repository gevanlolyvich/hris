<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Meeting as LocalMeeting;
use App\Models\MeetingEmployee;
use Illuminate\Http\Request;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\GoogleCalendar\Event as GoogleEvent;

class MeetingController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('Manage Meeting')) {
            $employees = Employee::orderby('name', 'asc')->get();
            if (Auth::user()->type == 'employee') {
                $current_employee = Employee::where('user_id', '=', \Auth::user()->id)->first();
                $meetings         = LocalMeeting::orderBy('meetings.id', 'desc')
                    ->leftjoin('meeting_employees', 'meetings.id', '=', 'meeting_employees.meeting_id')
                    ->where('meeting_employees.employee_id', '=', $current_employee->id)
                    ->orWhere(function ($q) {
                        $q->where('meetings.department_id', '["0"]')
                            ->where('meetings.employee_id', '["0"]');
                    })->get();
            } else {
                $meetings = !empty(\Auth::user()->branch_id) ? LocalMeeting::where('branch_id', \Auth::user()->branch_id)->get() : LocalMeeting::get();
            }

            return view('meeting.index', compact('meetings', 'employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Meeting')) {
            if (Auth::user()->type == 'employee') {
                $employees = Employee::where('is_created', 1)->where('user_id', '!=', \Auth::user()->id)->orderby('name', 'asc')->get()->pluck('name', 'id');
            } else {
                $branch      = !empty(\Auth::user()->branch_id) ? Branch::where('id', \Auth::user()->branch_id)->get() : Branch::get();
                $departments = !empty(\Auth::user()->branch_id) ? Department::where('branch_id', \Auth::user()->branch_id)->get() : Department::get();
                $employees   = !empty(\Auth::user()->branch_id) ? Employee::where('branch_id', \Auth::user()->branch_id)->orderby('name', 'asc')->get()->pluck('name', 'id') : Employee::orderby('name', 'asc')->get()->pluck('name', 'id');


                $meeting_types = ['Offline'=>'Offline', 'Online'=>'Online', 'Hybrid'=>'Hybrid'];
                // Log::info(json_encode($branch, JSON_PRETTY_PRINT));
                // Log::info(json_encode($employees, JSON_PRETTY_PRINT));
            }

            return view('meeting.create', compact('employees', 'departments', 'branch', 'meeting_types'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {

        $validator = \Validator::make(
            $request->all(),
            [
                'branch_id' => 'required',
                'department_id' => 'required',
                'employee_id' => 'required',
                'title' => 'required',
                'meeting_type' => 'required',
                'start_time' => 'required',
                'end_time' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        if (\Auth::user()->can('Create Meeting')) {
            $meeting                = new LocalMeeting();
            $meeting->branch_id     = $request->branch_id;
            $meeting->department_id = json_encode($request->department_id);
            $meeting->employee_id   = json_encode($request->employee_id);
            $meeting->title         = $request->title;
            $meeting->meeting_type  = $request->meeting_type;
            $meeting->url           = $request->url;
            $meeting->password      = $request->password;
            $meeting->start_time    = $request->start_time;
            $meeting->end_time      = $request->end_time;
            $meeting->location      = $request->location;
            $meeting->note          = $request->note;
            $meeting->created_by    = \Auth::user()->id;
            $meeting->save();


            // slack 
            $setting = Utility::settings();
            $branch = Branch::find($request->branch_id);
            if (isset($setting['meeting_notification']) && $setting['meeting_notification'] == 1) {
                $msg = $request->title . ' ' . __("meeting created for") . ' ' . $branch->name . ' ' . ("from") . ' ' . $request->date . ' ' . ("at") . ' ' . $request->time . '.';
                Utility::send_slack_msg($msg);
            }

            // telegram
            $setting = Utility::settings();
            $branch = Branch::find($request->branch_id);
            if (isset($setting['telegram_meeting_notification']) && $setting['telegram_meeting_notification'] == 1) {
                $msg = $request->title . ' ' . __("meeting created for") . ' ' . $branch->name . ' ' . ("from") . ' ' . $request->date . ' ' . ("at") . ' ' . $request->time . '.';
                Utility::send_telegram_msg($msg);
            }

            if (in_array('0', $request->employee_id)) {
                $departmentEmployee = Employee::whereIn('department_id', $request->department_id)->get()->pluck('id');
                // $departmentEmployee = $departmentEmployee;
            } else {

                $departmentEmployee = $request->employee_id;
            }
            foreach ($departmentEmployee as $employee) {
                $meetingEmployee              = new MeetingEmployee();
                $meetingEmployee->meeting_id  = $meeting->id;
                $meetingEmployee->employee_id = $employee;
                $meetingEmployee->created_by  = \Auth::user()->id;
                $meetingEmployee->save();
            }

            // google calendar
            if ($request->get('synchronize_type')  == 'google_calender') {

                $type = 'meeting';
                $request1 = new GoogleEvent();
                $request1->title = $request->title;
                $request1->start_date = $request->date;
                $request1->end_date = $request->date;

                Utility::addCalendarData($request1, $type);
            }

            return redirect()->route('meeting.index')->with('success', __('Meeting  successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show($id)
    {
        $meetings = LocalMeeting::find($id);
        $branch = Branch::find($meetings->branch_id);
        $employees = Employee::whereIn('id', json_decode($meetings->employee_id))->get()->pluck('name')->toArray();
        $departments = Department::whereIn('id', json_decode($meetings->department_id))->get()->pluck('name')->toArray();
        return view('meeting.show', compact('meetings', 'employees', 'departments', 'branch'));
        // return redirect()->route('meeting.index');
    }

    public function edit($meeting)
    {
        if (\Auth::user()->can('Edit Meeting')) {
            $meeting = LocalMeeting::find($meeting);
            $meeting_types = ['Offline'=>'Offline', 'Online'=>'Online', 'Hybrid'=>'Hybrid'];
            if ($meeting->created_by == Auth::user()->id) {
                if (Auth::user()->type == 'employee') {
                    $employees = Employee::where('is_active', 1)->where('user_id', '!=', Auth::user()->id)->orderby('name', 'asc')->get()->pluck('name', 'id');
                } else {
                    $employees = Employee::where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id');
                }

                return view('meeting.edit', compact('meeting', 'employees', 'meeting_types'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, LocalMeeting $meeting)
    {
        if (\Auth::user()->can('Edit Meeting')) {
            $validator = \Validator::make(
                $request->all(),
                [

                    'title' => 'required',
                    'meeting_type' => 'required',
                    'start_time' => 'required',
                    'end_time' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            if ($meeting->created_by == \Auth::user()->id || \Auth::user()->type == 'company') {
                $meeting->title         = $request->title;
                $meeting->meeting_type  = $request->meeting_type;
                $meeting->url           = $request->url;
                $meeting->password      = $request->password;
                $meeting->location      = $request->location;
                $meeting->start_time    = $request->start_time;
                $meeting->end_time      = $request->end_time;
                $meeting->note          = $request->note;
                $meeting->save();

                return redirect()->route('meeting.index')->with('success', __('Meeting successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(LocalMeeting $meeting)
    {
        if (\Auth::user()->can('Delete Meeting')) {
            if ($meeting->created_by == \Auth::user()->creatorId()) {
                $meeting->delete();

                return redirect()->route('meeting.index')->with('success', __('Meeting successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getdepartment(Request $request)
    {

        if ($request->branch_id == 0) {
            $departments = !empty(\Auth::user()->branch_id) ? Department::where('branch_id', \Auth::user()->branch_id)->get()->pluck('name', 'id')->toArray() : Department::get()->pluck('name', 'id')->toArray();
        } else {
            $departments = Department::where('branch_id', $request->branch_id)->get()->pluck('name', 'id')->toArray();
        }

        return response()->json($departments);
    }

    public function getemployee(Request $request)
    {
        if($request->department_id)
        {
            
            $employees = Employee::whereIn('department_id', $request->department_id)->orderby('name', 'asc')->get()->pluck('name', 'id')->toArray();
        }
        else
        {
            $employees = !empty(\Auth::user()->branch_id) ? Employee::where('branch_id', \Auth::user()->branch_id)->orderby('name', 'asc')->get()->pluck('name', 'id')->toArray() : Employee::orderby('name', 'asc')->get()->pluck('name', 'id')->toArray();
            
        }
        return response()->json($employees);
    }

    public function calender()
    {
        $employees = Employee::orderby('name', 'asc')->get();
            if (Auth::user()->type == 'employee') {
                $current_employee = Employee::where('user_id', '=', \Auth::user()->id)->first();
                $meetings         = LocalMeeting::orderBy('meetings.id', 'desc')
                    ->leftjoin('meeting_employees', 'meetings.id', '=', 'meeting_employees.meeting_id')
                    ->where('meeting_employees.employee_id', '=', $current_employee->id)
                    ->orWhere(function ($q) {
                        $q->where('meetings.department_id', '["0"]')
                            ->where('meetings.employee_id', '["0"]');
                    })
                    ->get();
            } else {
                $meetings = LocalMeeting::get();
            }

        return view('meeting.calender' , compact('meetings', 'employees'));
    }

    public function get_meeting_data(Request $request)
    {
        $arrayJson = [];
        if($request->get('calender_type') == 'google_calender')
        {
            $type ='meeting';
            $arrayJson =  Utility::getCalendarData($type);
            // dd($type, $arrayJson);
        }
        else
        {
            $data = LocalMeeting::get();
            
            foreach($data as $val)
            {
                if (Auth::user()->type == 'employee') {
                    $url = route('meeting.show', $val['id']);
                }else{
                    $url = route('meeting.edit', $val['id']);
                }
                $end_date=date_create($val->end_date);
                date_add($end_date,date_interval_create_from_date_string("1 days"));
                $arrayJson[] = [
                    "id"=> $val->id,
                    "title" => $val->title,
                    "start" => $val->date,
                    "end" => $val->date,
                    "className" => $val->color,
                    "textColor" => '#FFF',
                    "allDay" => true,
                    "url"=> $url,
                    // "url"=> URL::to('meeting/' . $val->id . '/edit'),
                ];
            }
        }
        // dd($arrayJson);
        return $arrayJson;
    }

}
