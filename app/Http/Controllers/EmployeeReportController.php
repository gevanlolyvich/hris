<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Report;
use App\Models\ReportAccomplishment;
use App\Models\ReportActivity;
use App\Models\ReportAttachment;
use App\Models\ReportObstacle;
use App\Models\ReportPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EmployeeReportController extends Controller
{
    public function index(Request $request)
    {
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

        if(Auth::user()->type == 'employee')
        {
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

                $reports = Report::whereIn('employee_id', $employees);
            } else {
                $reports = Report::where('employee_id', $emp);
            }
        }
        else
        {
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

            $reports = Report::whereIn('employee_id', $employee);
        }

        if ($request->type) {
            $reports = $reports->where('type', $request->type);
        }

        $type   = Report::$report_type;
        array_splice($type, 3, 1);
        $count = 1;
        foreach ($type as $index => $name) {
            $type[$index] = $count. '. ' . __($name);
            $count += 1;
        }

        $branch_count = 2;
        foreach ($branch as $index => $b) {
            if ($b == 'Head Office') {
                $branch[$index] = '1. '.  $b;
            } else {
                $branch[$index] = $branch_count. '. ' . __($b);
                $branch_count += 1;
            }
        }

        $reports = $reports->OrderBy('start_date', 'DESC')->get();

        return view('employee_report.index', compact('reports', 'branch', 'department', 'type'));
    }

    public function create()
    {
        if (\Auth::user()->type == 'employee') {
            $type   = Report::$report_type;
            array_splice($type, 3, 1);
            $count = 1;
            foreach ($type as $index => $name) {
                $type[$index] = $count. '. ' . __($name);
                $count += 1;
            }

            return view('employee_report.create', compact('type'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'))->withInput();
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
                [
                    'type'     => 'required',
                    'start_date'  => 'required|date',
                    'end_date'  => 'required|date|after_or_equal:start_date',
                    'activity' => 'nullable|array|same:activity',
                    'activity_date' => 'nullable|array|same:activity_date',
                    'attachment.*' => 'nullable|mimes:jpeg,png,jpg,gif,svg,pdf,doc,zip,docx,xls,xlsx,ppt,pptx|max:10480'
                ],
                [
                    'type.required'=> __('Type Column Must Be Filled'),
                    'start_date.required'=> __('Start Date Column Must Be Filled'),
                    'end_date.required'=> __('End Date Column Must Be Filled'),
                    'start_date.date'=> __('Start Date Input Must Be A Date'),
                    'end_date.date'=> __('End Date Input Must Be A Date'),
                    'end_date.after_or_equal'=> __('End Date Inputed Date Must Be After Or Equal With Start Date'),
                ]
            );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            $messages = implode('</br>', $messages->all());
            return redirect()->back()->with('error', $messages)->withInput();
        }

        // validate and preparing activity
        $activities = [];
        for ($i=0; $i < count($request->activity); $i++) { 
            if (($request->activity[$i] == null && $request->activity_date[$i] != null) || ($request->activity[$i] != null && $request->activity_date[$i] == null)) {
                return redirect()->back()->with('error', __('Activity Data Invalid'))->withInput();
            }

            if ($request->activity[$i] && $request->activity_date[$i]) {
                $activities[] = ['date' => $request->activity_date[$i], 'activity' => $request->activity[$i]];
            }
        }

        // validate and preparing accomplishment
        $accomplishments = [];
        foreach ($request->accomplishment as $accomplishment) {
            if ($accomplishment != null) {
                $accomplishments[]  = ['accomplishment' => $accomplishment];
            }
        }

        // validate and preparing obstacle
        $obstacles = [];
        foreach ($request->obstacle as $obstacle) {
            if ($obstacle != null) {
                $obstacles[]  = ['obstacle' => $obstacle];
            }
        }

        // validate and preparing plan
        $plans = [];
        foreach ($request->plan as $plan) {
            if ($plan != null) {
                $plans[]  = ['plan' => $plan];
            }
        }
        
        // validate and preparing attachment
        $employee = Auth::user()->employee;
        $attachments = [];
        if ($request->hasFile('attachment')) {
            for ($index=0; $index < count($request->attachment); $index++) { 
                $docs           = $request->attachment[$index];
                $emp_name       = preg_replace('/\s+/', '', $employee->name);
                $docName        = time() . "_" . date('Y-m-d') . "_" . $emp_name . '_' . $index . "." . $docs->getClientOriginalExtension();
                $path           = $docs->storeAs('uploads/report/' . $emp_name, $docName, 'public');
                $document_path  = env('APP_URL') . '/storage/' . $path;
                $attachments[]  = ['attachment' => $document_path];
            }
        }

        // create new report
        $report                 = new Report();
        $report->employee_id    = $employee->id;
        $report->created_by     = Auth::user()->id;
        $report->type           = $request->type;
        $report->start_date     = $request->start_date;
        $report->end_date       = $request->end_date;
        $report->save();

        // save report components
        for ($i=0; $i < count($activities); $i++) { 
            $activities[$i]['report_id'] = $report->id;
            $activities[$i]['created_at'] = date('Y-m-d H:i:s');
            $activities[$i]['updated_at'] = date('Y-m-d H:i:s');
        }
        for ($i=0; $i < count($accomplishments); $i++) { 
            $accomplishments[$i]['report_id'] = $report->id;
            $accomplishments[$i]['created_at'] = date('Y-m-d H:i:s');
            $accomplishments[$i]['updated_at'] = date('Y-m-d H:i:s');
        }
        for ($i=0; $i < count($obstacles); $i++) { 
            $obstacles[$i]['report_id'] = $report->id;
            $obstacles[$i]['created_at'] = date('Y-m-d H:i:s');
            $obstacles[$i]['updated_at'] = date('Y-m-d H:i:s');
        }
        for ($i=0; $i < count($plans); $i++) { 
            $plans[$i]['report_id'] = $report->id;
            $plans[$i]['created_at'] = date('Y-m-d H:i:s');
            $plans[$i]['updated_at'] = date('Y-m-d H:i:s');
        }
        for ($i=0; $i < count($attachments); $i++) { 
            $attachments[$i]['report_id'] = $report->id;
            $attachments[$i]['created_at'] = date('Y-m-d H:i:s');
            $attachments[$i]['updated_at'] = date('Y-m-d H:i:s');
        }
        
        // create components data related date to report
        ReportActivity::insert($activities);
        ReportAccomplishment::insert($accomplishments);
        ReportObstacle::insert($obstacles);
        ReportPlan::insert($plans);
        ReportAttachment::insert($attachments);
        
        return redirect()->route('employee-report.index')->with('success', __('Report Successfully Created'));
    }

    public function show($report_id)
    {
        $report = Report::find($report_id);

        // Set status to be read, if direct supervisor read the report
        if (Auth::user()->employee?->id == $report->employee->managed_by) {
            $report->is_read = true;
            $report->save();
        }

        $type   = Report::$report_type;
        array_splice($type, 3, 1);
        foreach ($type as $index => $name) {
            $type[$index] = __($name);
        }
        return view('employee_report.show', compact('report', 'type'));
    }

    public function edit($report_id)
    {
        $report = Report::find($report_id);
        $type   = Report::$report_type;
        array_splice($type, 3, 1);
        $count = 1;
        foreach ($type as $index => $name) {
            $type[$index] = $count. '. ' . __($name);
            $count += 1;
        }
        return view('employee_report.edit', compact('report', 'type'));
    }

    public function update(Request $request, $report_id)
    {
        $validator = Validator::make(
            $request->all(),
                [
                    'type'     => 'required',
                    'start_date'  => 'required|date',
                    'end_date'  => 'required|date|after_or_equal:start_date',
                    'activity' => 'nullable|array|same:activity',
                    'activity_date' => 'nullable|array|same:activity_date',
                    'attachment.*' => 'nullable|mimes:jpeg,png,jpg,gif,svg,pdf,doc,zip,docx,xls,xlsx,ppt,pptx|max:10480'
                ],
                [
                    'type.required'=> __('Type Column Must Be Filled'),
                    'start_date.required'=> __('Start Date Column Must Be Filled'),
                    'end_date.required'=> __('End Date Column Must Be Filled'),
                    'start_date.date'=> __('Start Date Input Must Be A Date'),
                    'end_date.date'=> __('End Date Input Must Be A Date'),
                    'end_date.after_or_equal'=> __('End Date Inputed Date Must Be After Or Equal With Start Date'),
                ]
            );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            $messages = implode('</br>', $messages->all());
            return redirect()->back()->with('error', $messages)->withInput();
        }

        $report         = Report::find($report_id);

        //* Prepare Activities
        $new_activities = [];
        for ($i=0; $i < count($request->new_activity); $i++) { 
            if (($request->new_activity[$i] == null && $request->new_activity_date[$i] != null) || ($request->new_activity[$i] != null && $request->new_activity_date[$i] == null)) {
                return redirect()->back()->with('error', __('Activity Data Invalid'))->withInput();
            }

            if ($request->new_activity[$i] && $request->new_activity_date[$i]) {
                $new_activities[] = ['date' => $request->new_activity_date[$i], 'activity' => $request->new_activity[$i]];
            }
        }
        $old_activity_id    = $report->activities->pluck('id')->toArray();
        
        $perve_activity_id  = array_keys($request->activity ?? []);

        // updating old activity
        if (!isset($request->activity)) {
            $request['activity'] = [];
        }
        foreach ($request->activity as $index => $activity) {
            if ($activity != null && $request->activity_date[$index] != null) {
                $activity_instance           = ReportActivity::find($index);
                $activity_instance->date     = $request->activity_date[$index];
                $activity_instance->activity = $activity;
                $activity_instance->save();
            }
        }
        
        // delete unused activity
        $delete_activity_id = array_diff($old_activity_id, $perve_activity_id);
        ReportActivity::whereIn('id', $delete_activity_id)->where('report_id', $report_id)->delete();
        
        // create new activity
        for ($i=0; $i < count($new_activities); $i++) { 
            $new_activities[$i]['report_id']    = $report_id;
            $new_activities[$i]['created_at']   = date('Y-m-d H:i:s');
            $new_activities[$i]['updated_at']   = date('Y-m-d H:i:s');
        }
        ReportActivity::insert($new_activities);
        
        //* Prepare Accomplishment
        $new_accomplishment = [];
        if (!isset($request->new_accomplishment)) {
            $request['new_accomplishment'] = [];
        }
        foreach ($request->new_accomplishment as $accomplishment) {
            if ($accomplishment != null) {
                $new_accomplishment[]  = ['accomplishment' => $accomplishment];
            }
        }
        
        $old_accomplishment_id    = $report->accomplishments->pluck('id')->toArray();
        $perve_accomplishment_id  = array_keys($request->accomplishment ?? []);

        // Create new accomplishment
        for ($i=0; $i < count($new_accomplishment); $i++) { 
            $new_accomplishment[$i]['report_id']    = $report_id;
            $new_accomplishment[$i]['created_at']   = date('Y-m-d H:i:s');
            $new_accomplishment[$i]['updated_at']   = date('Y-m-d H:i:s');
        }
        ReportAccomplishment::insert($new_accomplishment);
        
        // Update accomplishment
        if (!isset($request->accomplishment)) {
            $request['accomplishment'] = [];
        }
        foreach ($request->accomplishment as $index => $accomplishment) {
            if ($accomplishment != null) {
                $accomplishment_instance                  = ReportAccomplishment::find($index);
                $accomplishment_instance->accomplishment  = $accomplishment;
                $accomplishment_instance->save();
            }
        }
        
        // Delete accomplishment
        $delete_accomplishment_id = array_diff($old_accomplishment_id, $perve_accomplishment_id);
        ReportAccomplishment::whereIn('id', $delete_accomplishment_id)->where('report_id', $report_id)->delete();

        //* Prepare obstacles
        $new_obstacles = [];
        if (!isset($request->new_obstacle)) {
            $request['new_obstacle'] = [];
        }
        foreach ($request->new_obstacle as $obstacle) {
            if ($obstacle != null) {
                $new_obstacles[]  = ['obstacle' => $obstacle];
            }
        }

        $old_obstacle_id    = $report->obstacles->pluck('id')->toArray();
        $perve_obstacle_id  = array_keys($request->obstacle ?? []);

        // Create new obstacle
        for ($i=0; $i < count($new_obstacles); $i++) { 
            $new_obstacles[$i]['report_id']  = $report->id;
            $new_obstacles[$i]['created_at'] = date('Y-m-d H:i:s');
            $new_obstacles[$i]['updated_at'] = date('Y-m-d H:i:s');
        }
        ReportObstacle::insert($new_obstacles);

        // Update obstacle
        if (!isset($request->obstacle)) {
            $request['obstacle'] = [];
        }
        foreach ($request->obstacle as $index => $obstacle) {
            if ($obstacle != null) {
                $obstacle_instance              = ReportObstacle::find($index);
                $obstacle_instance->obstacle    = $obstacle;
                $obstacle_instance->save();
            }
        }

        // Delete obstacle
        $delete_obstacle_id = array_diff($old_obstacle_id, $perve_obstacle_id);
        ReportObstacle::whereIn('id', $delete_obstacle_id)->where('report_id', $report_id)->delete();

        //* Prepare Plan
        $new_plans = [];
        if (!isset($request->new_plan)) {
            $request['new_plan'] = [];
        }
        foreach ($request->new_plan as $plan) {
            if ($plan != null) {
                $new_plans[]  = ['plan' => $plan];
            }
        }

        $old_plan_id    = $report->plans->pluck('id')->toArray();
        $perve_plan_id  = array_keys($request->plan ?? []);

        // create plan
        for ($i=0; $i < count($new_plans); $i++) { 
            $new_plans[$i]['report_id']  = $report->id;
            $new_plans[$i]['created_at'] = date('Y-m-d H:i:s');
            $new_plans[$i]['updated_at'] = date('Y-m-d H:i:s');
        }
        ReportPlan::insert($new_plans);

        // update plan
        if (!isset($request->plan)) {
            $request['plan'] = [];
        }
        foreach ($request->plan as $index => $plan) {
            if ($plan != null) {
                $plan_instance              = ReportPlan::find($index);
                $plan_instance->plan        = $plan;
                $plan_instance->save();
            }
        }

        // delete plan
        $delete_plan_id = array_diff($old_plan_id, $perve_plan_id);
        ReportPlan::whereIn('id', $delete_plan_id)->where('report_id', $report_id)->delete();

        //* Prepare Attachment
        $employee           = Auth::user()->employee;
        $total_attachment   = $report->attachments->count();
        $emp_name           = preg_replace('/\s+/', '', $employee->name);

        // Create new attachment
        $new_attachments = [];
        if (!isset($request->new_attachment)) {
            $request['new_attachment'] = [];
        }
        if ($request->hasFile('new_attachment')) {
            foreach ($request->new_attachment as $index => $new_attachment) {
                $docs               = $new_attachment;
                $docName            = time() . "_" . date('Y-m-d') . "_" . $emp_name . '_' . $index + $total_attachment . "." . $docs->getClientOriginalExtension();
                $path               = $docs->storeAs('uploads/report/' . $emp_name, $docName, 'public');
                $document_path      = env('APP_URL') . '/storage/' . $path;
                $new_attachments[]  = ['attachment' => $document_path];
            }
        }
        for ($i=0; $i < count($new_attachments); $i++) { 
            $new_attachments[$i]['report_id']  = $report->id;
            $new_attachments[$i]['created_at'] = date('Y-m-d H:i:s');
            $new_attachments[$i]['updated_at'] = date('Y-m-d H:i:s');
        }
        ReportAttachment::insert($new_attachments);

        // Update attachment
        if ($request->hasFile('attachment')) {
            if (!isset($request->attachment)) {
                $request['attachment'] = [];
            }
            foreach($request->attachment as $index => $attachment) {
                $attachment_instance    = ReportAttachment::find($index);

                $filepath_array         = explode('/', $attachment_instance->attachment);
                $filename               = array_pop($filepath_array);
                
                // Check if the file exists before attempting to delete
                if (Storage::disk('public')->exists("uploads/report/$emp_name/$filename")) {
                    Storage::disk('public')->delete("uploads/report/$emp_name/$filename");
                }

                $docs                               = $attachment;
                $docName                            = time() . "_" . date('Y-m-d') . "_" . $emp_name . '_0' . $index . "." . $docs->getClientOriginalExtension();
                $path                               = $docs->storeAs('uploads/report/' . $emp_name, $docName, 'public');
                $document_path                      = env('APP_URL') . '/storage/' . $path;
                $attachment_instance->attachment    = $document_path;
                $attachment_instance->save();
            }
        }

        // delete attachment
        $delete_attachments = ReportAttachment::where('report_id', $report_id)->whereIn('id', $request->delete_attachment ?? [])->get();
        foreach ($delete_attachments as $delete_attachment) {
            $delete_filepath_array         = explode('/', $delete_attachment->attachment);
            $delete_filename               = array_pop($delete_filepath_array);
            
            // Check if the file exists before attempting to delete
            if (Storage::disk('public')->exists("uploads/report/$emp_name/$delete_filename")) {
                Storage::disk('public')->delete("uploads/report/$emp_name/$delete_filename");
            }
        }
        ReportAttachment::where('report_id', $report_id)->whereIn('id', $request->delete_attachment ?? [])->delete();

        $report->type       = $request->type;
        $report->start_date = $request->start_date;
        $report->end_date   = $request->end_date;
        $report->save();

        return redirect()->route('employee-report.index')->with('success', __('Report Successfully Updated'));
    }

    public function destroy($report)
    {
        $report = Report::find($report);
        if (Auth::user()->id == $report->created_by || Auth::user()->type == 'company') {
            $employee_name      = preg_replace('/\s+/', '', $report->user->name);

            // deleting uploaded file
            foreach ($report->attachments as $attachment) {
                $filepath_array = explode('/', $attachment->attachment);
                $filename = array_pop($filepath_array);
                
                // Check if the file exists before attempting to delete
                if (Storage::disk('public')->exists("uploads/report/$employee_name/$filename")) {
                    Storage::disk('public')->delete("uploads/report/$employee_name/$filename");
                }
            }

            // deleting data
            ReportAccomplishment::where('report_id', $report->id)->delete();
            ReportActivity::where('report_id', $report->id)->delete();
            ReportAttachment::where('report_id', $report->id)->delete();
            ReportObstacle::where('report_id', $report->id)->delete();
            ReportPlan::where('report_id', $report->id)->delete();
            $report->delete();

            return redirect()->back()->with('success', __('Report Successfully Deleted'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function response(Request $request, $report_id)
    {
        $report     = Report::find($report_id);
        if (Auth::user()->employee?->id != $report->employee_id) {
            $report->response      = $request->response;
            $report->response_by   = Auth::user()->id;
            $report->save();

            return redirect()->back()->with('success', __('Report Response Successfully Sent'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'))->withInput();
        }
    }
}
