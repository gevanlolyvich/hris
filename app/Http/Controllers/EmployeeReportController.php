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
        foreach ($type as $index => $name) {
            $type[$index] = __($name);
        }

        $reports = $reports->OrderBy('start_date', 'DESC')->get();

        return view('employee_report.index', compact('reports', 'branch', 'department', 'type'));
    }

    public function create()
    {
        if (\Auth::user()->type == 'employee') {
            $type   = Report::$report_type;
            foreach ($type as $index => $name) {
                $type[$index] = __($name);
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
        Log::info($report_id);
        Log::info($report);
        $type   = Report::$report_type;
        foreach ($type as $index => $name) {
            $type[$index] = __($name);
        }
        return view('employee_report.show', compact('report', 'type'));
    }

    public function edit(Report $report)
    {
    }

    public function update(Request $request, Report $report)
    {
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
}
