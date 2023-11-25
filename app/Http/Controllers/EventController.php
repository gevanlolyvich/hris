<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Event as LocalEvent ;
use App\Models\EventEmployee;
use App\Models\Projects;
use App\Models\Tasks;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\GoogleCalendar\Event as GoogleEvent;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('Manage Event')) {
            $events    = LocalEvent::where('created_by', \Auth::user()->creatorId())->get();
            $current_month_event = null;
            $today_date = date('m');

            if (\Auth::user()->type != 'employee') {
                $current_month_event = LocalEvent::select('id','start_date','end_date', 'title', 'created_at','color')
                    ->whereNotNull(['start_date','end_date'])
                    ->Where(
                        function ($q) use ($today_date) {
                            $q->whereMonth('start_date',$today_date)
                              ->orWhereMonth('end_date',$today_date);
                        }
                    )->get();
            } else {
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

                $current_month_event = LocalEvent::select('events.id','events.start_date','events.end_date', 'events.title', 'events.created_at','events.color')
                    ->leftjoin('event_employees', 'events.id', '=', 'event_employees.event_id')
                    ->whereIn('event_employees.employee_id', $employee_id)
                    ->whereNotNull(['start_date','end_date'])
                    ->Where(
                        function ($q) use ($today_date) {
                            $q->whereMonth('start_date',$today_date)
                              ->orWhereMonth('end_date',$today_date);
                        }
                    )->distinct()->get();
            }
            $arrEvents = [];
            foreach ($events as $event) {
                $arr['id']    = $event['id'];
                $arr['title'] = $event['title'];
                $arr['start'] = $event['start_date'];
                $arr['end']   = $event['end_date'];
                //                $arr['allDay']    = !0;
                //                $arr['className'] = 'bg-danger';
                $arr['className'] = $event['color'];
                // $arr['borderColor']     = "#fff";
                // $arr['textColor']       = "white";
                $arr['url']             = route('event.edit', $event['id']);

                $arrEvents[] = $arr;
            }
            // $arrEvents = str_replace('"[', '[', str_replace(']"', ']', json_encode($arrEvents)));
            $arrEvents =  json_encode($arrEvents);

            return view('event.index', compact('arrEvents','current_month_event','events'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Event')) {
            $employees   = Employee::where('created_by', '=', \Auth::user()->creatorId())->orderby('name', 'asc')->get()->pluck('name', 'id');
            $branch      = Branch::where('created_by', '=', \Auth::user()->creatorId())->get();
            $departments = Department::where('created_by', '=', \Auth::user()->creatorId())->get();

            return view('event.create', compact('employees', 'branch', 'departments'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Event')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'branch_id' => 'required',
                    'department_id' => 'required',
                    'employee_id' => 'required',
                    'title' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required',
                    'color' => 'required',
                    'location' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $document_path = null;
            if ($request->file('myDocument')) {
                $docs = $request->file('myDocument');
                $docName = time() . "_" . date('Y-m-d') . "_" . preg_replace('/\s+/', '', $request->title) . "." . $docs->getClientOriginalExtension();
                $path = $docs->storeAs('uploads/events', $docName, 'public');
                $document_path = env('APP_URL') . '/storage/' . $path;
            }

            $event                 = new LocalEvent();
            $event->branch_id      = $request->branch_id;
            $event->department_id  = json_encode($request->department_id);
            $event->employee_id    = json_encode($request->employee_id);
            $event->title          = $request->title;
            $event->start_date     = $request->start_date;
            $event->end_date       = $request->end_date;
            $event->color          = $request->color;
            $event->description    = $request->description;
            $event->document       = $document_path;
            $event->created_by     = \Auth::user()->creatorId();
            $event->location       = $request->location;
            $event->location_coord = "$request->latitude, $request->longitude";
            $event->save();

            // slack 
            $setting = Utility::settings();
            $branch = Branch::find($request->branch_id);
            if (isset($setting['event_notification']) && $setting['event_notification'] == 1) {
                $msg = $request->title . ' ' . __("for branch") . ' ' . $branch->name . ' ' . ("from") . ' ' . $request->start_date . ' ' . __("to") . ' ' . $request->end_date . '.';
                Utility::send_slack_msg($msg);
            }

            //telegram
            $setting = Utility::settings();
            $branch = Branch::find($request->branch_id);
            if (isset($setting['telegram_ticket_notification']) && $setting['telegram_ticket_notification'] == 1) {
                $msg = $request->title . ' ' . __("for branch") . ' ' . $branch->name . ' ' . ("from") . ' ' . $request->start_date . ' ' . __("to") . ' ' . $request->end_date . '.';
                Utility::send_telegram_msg($msg);
            }

            //twilio
            $setting = Utility::settings();
            $branch = Branch::find($request->branch_id);
            $departments = Department::where('branch_id', $request->branch_id)->first();
            $employees = Employee::where('employee_id', $request->employee_id)->first();

            if (isset($setting['twilio_event_notification']) && $setting['twilio_event_notification'] == 1) {
                $employeess = Employee::where('branch_id', $request->branch_id)->whereIn('employee_id', $request->employee_id)->orderby('name', 'asc')->get();
                foreach ($employeess as $key => $employee) {
                    $msg = $request->title . ' ' . __("for branch") . ' ' . $branch->name . ' ' . ("from") . ' ' . $request->start_date . ' ' . __("to") . ' ' . $request->end_date . '.';

                    Utility::send_twilio_msg($employee->phone, $msg);
                }
            }

            if (in_array('0', $request->employee_id)) {
                $departmentEmployee = Employee::whereIn('department_id', $request->department_id)->get()->pluck('id');
                // $departmentEmployee = $departmentEmployee;
            } else {
                $departmentEmployee = $request->employee_id;
            }
            foreach ($departmentEmployee as $employee) {
                $eventEmployee              = new EventEmployee();
                $eventEmployee->event_id    = $event->id;
                $eventEmployee->employee_id = $employee;
                $eventEmployee->created_by  = \Auth::user()->creatorId();
                $eventEmployee->save();
            }

            if ($request->get('synchronize_type')  == 'google_calender') {

                $type = 'event';
                $request1 = new GoogleEvent();
                $request1->title = $request->title;
                $request1->start_date = $request->start_date;
                $request1->end_date = $request->end_date;
                // dd($request1);
                Utility::addCalendarData($request1, $type);
            }

            return redirect()->route('event.index')->with('success', __('Event Successfully Created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show($event)
    {
        $event = LocalEvent::find($event);
        $event_employees = EventEmployee::where('event_id', $event->id)->get();
        return view('event.show', compact('event', 'event_employees'));
    }

    public function edit($event)
    {
        // if (\Auth::user()->can('Edit Event')) {
            $event                        = LocalEvent::find($event);
            $created_by                   = \Auth::user()->creatorId();
            $employees                    = Employee::where('created_by', '=', \Auth::user()->creatorId())->orderby('name', 'asc')->get()->pluck('name', 'id');
            $branch                       = Branch::where('created_by', '=', \Auth::user()->creatorId())->get();
            $departments                  = Department::where('created_by', '=', \Auth::user()->creatorId())->get();
            $event_employees              = EventEmployee::where('event_id', $event->id)->select('employee_id')->get()->pluck('employee_id');
            $selected_departments         = Department::whereIn('id', json_decode($event->department_id, true))->select('id', 'branch_id', 'name')->get();
            $selected_employees           = Employee::whereIn('id', json_decode($event->employee_id, true))->select('id', 'user_id', 'department_id', 'name')->get();
            list($latitude, $longitude)   = explode(', ', $event->location_coord ?? "0, 0");
            if ($event->created_by == $created_by) {
                return view('event.edit', compact('event', 'employees', 'branch', 'departments', 'latitude', 'longitude', 'selected_departments', 'selected_employees'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        // } else {
        //     return response()->json(['error' => __('Permission denied.')], 401);
        // }
    }

    public function update(Request $request, LocalEvent $event)
    {
        // return $request;
        if (\Auth::user()->can('Edit Event')) {
            if ($event->created_by == \Auth::user()->creatorId()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'title' => 'required',
                        'start_date' => 'required',
                        'end_date' => 'required',
                        'color' => 'required',
                        'location' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $document_path = null;
                if ($request->file('myDocument')) {
                    $docs = $request->file('myDocument');
                    $docName = time() . "_" . date('Y-m-d') . "_" . preg_replace('/\s+/', '', $request->title) . "." . $docs->getClientOriginalExtension();
                    $path = $docs->storeAs('uploads/events', $docName, 'public');
                    $document_path = env('APP_URL') . '/storage/' . $path;
                }

                // * Employee Data
                $request_employee_id = $request->employee_id ?? array();
                $to_be_remove_array = null;
                $presist_employee_array = null;
                foreach (json_decode($event->employee_id) as $empId) {
                    if (!in_array($empId, $request_employee_id)) {
                        $to_be_remove_array[] = $empId;
                    } else {
                        $presist_employee_array[] = $empId;
                    }
                }

                $new_employee_array = array_diff($request_employee_id, $presist_employee_array);

                // remove old not use event employee
                if ($to_be_remove_array) {
                    EventEmployee::where('event_id', $event->id)->whereIn('employee_id', $to_be_remove_array)->delete();
                }

                // create new event employee
                if ($new_employee_array) {
                    foreach ($new_employee_array as $employee) {
                        $eventEmployee              = new EventEmployee();
                        $eventEmployee->event_id    = $event->id;
                        $eventEmployee->employee_id = $employee;
                        $eventEmployee->created_by  = \Auth::user()->creatorId();
                        $eventEmployee->save();
                    }
                }

                $event->branch_id      = $request->branch_id;
                $event->department_id  = json_encode($request->department_id);
                $event->employee_id    = json_encode($request->employee_id);
                $event->title          = $request->title;
                $event->start_date     = $request->start_date;
                $event->end_date       = $request->end_date;
                $event->color          = $request->color;
                $event->description    = $request->description;
                $event->document       = $document_path ? $document_path : $event->document;
                $event->location       = $request->location;
                $event->location_coord = "$request->latitude, $request->longitude";
                $event->save();

                return redirect()->back()->with('success', __('Event successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(LocalEvent $event)
    {
        if (\Auth::user()->can('Delete Event')) {
            if ($event->created_by == \Auth::user()->creatorId()) {
                $event->delete();

                return redirect()->route('event.index')->with('success', __('Event successfully deleted.'));
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
            $departments = Department::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id')->toArray();
        } else {
            $departments = Department::where('created_by', '=', \Auth::user()->creatorId())->where('branch_id', $request->branch_id)->get()->pluck('name', 'id')->toArray();
        }

        return response()->json($departments);
    }

    public function getemployee(Request $request)
    {
        if (in_array('0', $request->department_id)) {
            $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->orderby('name', 'asc')->get()->pluck('name', 'id')->toArray();
        } else {
            $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->whereIn('department_id', $request->department_id)->orderby('name', 'asc')->get()->pluck('name', 'id')->toArray();
        }

        return response()->json($employees);
    }

    public function showData($id)
    {   
        $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->orderby('name', 'asc')->get()->pluck('name', 'id');
        $event = LocalEvent::find($id);
            
          return view('event.edit', compact('event', 'employees'));
    }

    public function get_event_data(Request $request)
    {   
        $arrayJson = [];
        if($request->get('calender_type') == 'google_calender')
        {
            $type ='event';
            $arrayJson =  Utility::getCalendarData($type);
        }
        else
        {
            $data = null;
            if ($request->get('user_type') === 'employee' || \Auth::user()->type == 'employee') {
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

                $data = LocalEvent::leftjoin('event_employees', 'events.id', '=', 'event_employees.event_id')
                ->whereIn('event_employees.employee_id', $employee_id)
                ->orWhere(
                    function ($q) {
                        $q->where('events.department_id', '["0"]')->where('events.employee_id', '["0"]');
                    }
                )->groupBy('events.id')->get();
            } else {
                $data = LocalEvent::get();
            }

            foreach($data as $val)
            {
                $end_date=date_create($val->end_date);
                date_add($end_date,date_interval_create_from_date_string("1 days"));
                $arrayJson[] = [
                    "id"=> $val->event_id ?? $val->id,
                    "title" => $val->title,
                    "start" => $val->start_date,
                    "end" => date_format($end_date,"Y-m-d H:i:s"),
                    "className" => $val->color,
                    "allDay" => true,
                    "url"=> route('event.show', $val['event_id'] ?? $val['id']),

                ];
            }
        }

        return $arrayJson;
    }

}
