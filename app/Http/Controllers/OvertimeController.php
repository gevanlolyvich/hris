<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Overtime;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class OvertimeController extends Controller
{
    public function index(Request $request) {   
        $branch = !empty(\Auth::user()->branch_id) ? Branch::where('id', \Auth::user()->branch_id)->get()->pluck('name', 'id') : Branch::get()->pluck('name', 'id');

        $department = !empty(\Auth::user()->branch_id) ? Department::where('branch_id', \Auth::user()->branch_id)->get()->pluck('name', 'id') : Department::get()->pluck('name', 'id');

        if (empty(\Auth::user()->branch_id)) {
            $branch->prepend('All', '');
            $department->prepend('All', '');
        }

        $overtimes = null;

        // employee filter
        if (\Auth::user()->type == 'employee') {
            $emp = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;

            $userId = \Auth::user()->employee->user_id;
            $subordinates = \Auth::user()->employee->subordinatesFlatten();
            $employees = collect();
            
            // Check if employee managing other employee or not
            if ($subordinates->isNotEmpty()) {
                foreach ($subordinates as $subordinate) {
                    $employees->push($subordinate->id);
                }
            } else {
                $employees->push($emp);
            }

            $overtimes = Overtime::whereIn('employee_id', $employees);
        } else {
            $employees = !empty(\Auth::user()->branch_id) ? Employee::where('branch_id', \Auth::user()->branch_id)->select('id')->get()->pluck('id') : Employee::select('id')->get()->pluck('id');
            $overtimes = Overtime::whereIn('employee_id', $employees);
        }

        // time filter
        if ($request->type == 'monthly' && !empty($request->month)) {
            $month = date('m', strtotime($request->month));
            $year  = date('Y', strtotime($request->month));

            $start_date = date($year . '-' . $month . '-01');
            $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

            // old date
            // $end_date   = date($year . '-' . $month . '-t');

            $overtimes->whereBetween(
                'date',
                [
                    $start_date,
                    $end_date,
                ]
            );
        } elseif ($request->type == 'daily' && !empty($request->date)) {
            $overtimes->where('date', $request->date);
        } else {
            $month      = date('m');
            $year       = date('Y');
            $start_date = date($year . '-' . $month . '-01');
            $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

            // old date
            // $end_date   = date($year . '-' . $month . '-t');

            $overtimes->whereBetween(
                'date',
                [
                    $start_date,
                    $end_date,
                ]
            );
        }

        $overtimes = $overtimes->orderBy('date', 'DESC')->get();

        return view('overtime.index', compact('overtimes', 'branch', 'department'));
    }

    public function create()
    {
        $employees = null;
        if (\Auth::user()->type == 'employee') {
            $subordinates = \Auth::user()->employee->subordinatesFlatten();

                // Check if employee managing other employee or not
                if ($subordinates->isNotEmpty()) {
                    $employeesId = collect();
                    foreach ($subordinates as $subordinate) {
                        $employeesId->push($subordinate->id);
                    }

                    $employees = Employee::where('is_active', 1)->whereIn('id', $employeesId)->get()->pluck('name', 'id');
                } else {
                    $employees = Employee::where('is_active', 1)->where('id',\Auth::user()->employee->id)->get()->pluck('name', 'id');
                }
        } else {
            $employees = !empty(\Auth::user()->branch_id) ? Employee::where('branch_id', \Auth::user()->branch_id)->where('is_active', 1)->get()->pluck('name', 'id') : Employee::where('is_active', 1)->get()->pluck('name', 'id');
        }
        return view('overtime.create', compact('employees'));
    }

    public function overtimeCreate($id)
    {
        $employee = Employee::where('is_active', 1)->find($id);

        return view('overtime.create', compact('employee'));
    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('Create Overtime'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'employee_id' => 'required',
                                   'title' => 'required',
                                   'date' => 'required',
                                   'overtimeDocument' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $overtime                 = new Overtime();
            $overtime->employee_id    = $request->employee_id;
            $overtime->title          = $request->title;
            $overtime->date           = $request->date;
            $overtime->description    = $request->description;
            $overtime->is_work_day    = $request->is_work_day == 'yes' ? true : false;
            $overtime->created_by     = \Auth::user()->id;

            $document_path = null;
            if ($request->file('overtimeDocument')) {
                $docs = $request->file('overtimeDocument');
                $docName = time() . "_" . date('Y-m-d') . "_" . preg_replace('/\s+/', '', $request->title) . "." . $docs->getClientOriginalExtension();
                $path = $docs->storeAs('uploads/overtimes', $docName, 'public');
                $document_path = env('APP_URL') . '/storage/' . $path;
            }
            $overtime->document       = $document_path;

            $overtime->save();

            return redirect()->back()->with('success', __('Overtime successfully created'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Overtime $overtime)
    {
        return redirect()->route('commision.index');
    }

    public function edit($overtime)
    {
        $overtime = Overtime::find($overtime);
        if(\Auth::user()->can('Edit Overtime'))
        {
            if($overtime->created_by == \Auth::user()->id || \Auth::user()->type != 'employee')
            {
                $employees = null;
                if (Auth::user()->type == 'employee') {
                    $subordinates = \Auth::user()->employee->subordinatesFlatten();

                    // Check if employee managing other employee or not
                    if ($subordinates->isNotEmpty()) {
                        $employeesId = collect();
                        foreach ($subordinates as $subordinate) {
                            $employeesId->push($subordinate->id);
                        }

                        $employees = Employee::where('is_active', 1)->whereIn('id', $employeesId)->get()->pluck('name', 'id');
                    } else {
                        $employees = Employee::where('is_active', 1)->where('id',\Auth::user()->employee->id)->get()->pluck('name', 'id');
                    }
                } else {
                    $employees  = !empty(\Auth::user()->branch_id) ? Employee::where('branch_id', \Auth::user()->branch_id)->where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id') : Employee::where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id');
                }
                return view('overtime.edit', compact('overtime', 'employees'));
            }
            else
            {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, $overtime)
    {
        $overtime = Overtime::find($overtime);
        if(\Auth::user()->can('Edit Overtime'))
        {
            if($overtime->created_by == \Auth::user()->id || Auth::user()->type != 'employee')
            {
                $validator = \Validator::make(
                    $request->all(), [
                                        'employee_id' => 'required',
                                        'title' => 'required',
                                        'date' => 'required',
                                    ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $overtime->employee_id    = $request->employee_id;
                $overtime->title          = $request->title;
                $overtime->date           = $request->date;
                $overtime->description    = $request->description;
                $overtime->is_work_day    = $request->is_work_day == 'yes' ? true : false;

                if ($overtime->document && $request->file('overtimeDocument')) {
                    $filepath_array = explode('/', $overtime->document);
                    $filename = array_pop($filepath_array);
    
                    // Check if the file exists before attempting to delete
                    if (Storage::disk('public')->exists("uploads/overtimes/$filename")) {
                        Storage::disk('public')->delete("uploads/overtimes/$filename");
                    }
                }

                $document_path = null;
                if ($request->file('overtimeDocument')) {
                    $docs = $request->file('overtimeDocument');
                    $docName = time() . "_" . date('Y-m-d') . "_" . preg_replace('/\s+/', '', $request->title) . "." . $docs->getClientOriginalExtension();
                    $path = $docs->storeAs('uploads/overtimes', $docName, 'public');
                    $document_path = env('APP_URL') . '/storage/' . $path;
                }
                $overtime->document       = $document_path ? $document_path : $overtime->document;

                $overtime->save();

                return redirect()->back()->with('success', __('Overtime successfully updated.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Overtime $overtime)
    {
        if(\Auth::user()->can('Delete Overtime'))
        {
            if($overtime->created_by == \Auth::user()->id || Auth::user()->type != 'employee')
            {

                if ($overtime->document) {
                    $filepath_array = explode('/', $overtime->document);
                    $filename = array_pop($filepath_array);
    
                    // Check if the file exists before attempting to delete
                    if (Storage::disk('public')->exists("uploads/overtimes/$filename")) {
                        Storage::disk('public')->delete("uploads/overtimes/$filename");
                    }
                }

                $overtime->delete();

                return redirect()->back()->with('success', __('Overtime successfully deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function attendance(Request $request)
    {
        $overtime = Overtime::find($request->overtimeId ?? $request->overtimeIdOut);

        $picture_path = null;
        $employee = Employee::where('is_active', 1)->where('user_id', Auth::user()->id)->first();

        if (empty($employee) || !$employee) {
            return redirect()->back()->with('error', __('Inactive'));
        }

        if ($request->out == '1' && $overtime) {
            // clock out
            Log::info('Clock Out');

            // delete old picture file
            if ($overtime->picture_out) {
                $filepath_array = explode('/', $overtime->picture_out);
                $filename = array_pop($filepath_array);

                // Check if the file exists before attempting to delete
                if (Storage::disk('public')->exists("uploads/overtimes/$overtime->id/attendance/clock_out/$filename")) {
                    Storage::disk('public')->delete("uploads/overtimes/$overtime->id/attendance/clock_out/$filename");
                }
            }

            // process image file
            if ($request->input('picture_out')) {
                $base64ImageData = $request->input('picture_out');
                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64ImageData));
                $pictureName = 'attendance_'.time().'_'.date('Y-m-d').'_'.preg_replace('/\s+/', '', $employee->name).'.png';
                Storage::disk('public')->put("uploads/overtimes/$overtime->id/attendance/clock_out/$pictureName", $imageData);
                $picture_path = env('APP_URL') . "/storage/uploads/overtimes/$overtime->id/attendance/clock_out/$pictureName";
            }

            $overtime->clock_out   = date('Y-m-d H:i:s');
            $overtime->coord_out   = "$request->latitude, $request->longitude, $request->accuracy";
            $overtime->picture_out = $picture_path;
            $overtime->save();

            return redirect()->back()->with('success', __('Attendance Successfully Added'));
        } elseif ($overtime) {
            // clock in
            Log::info('Clock In');

            // delete old picture file
            if ($overtime->picture_in) {
                $filepath_array = explode('/', $overtime->picture_in);
                $filename = array_pop($filepath_array);

                // Check if the file exists before attempting to delete
                if (Storage::disk('public')->exists("uploads/overtimes/$overtime->id/attendance/clock_in/$filename")) {
                    Storage::disk('public')->delete("uploads/overtimes/$overtime->id/attendance/clock_in/$filename");
                }
            }

            // process image file
            if ($request->input('picture')) {
                $base64ImageData = $request->input('picture');
                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64ImageData));
                $pictureName = 'attendance_'.time().'_'.date('Y-m-d').'_'.preg_replace('/\s+/', '', $employee->name).'.png';
                Storage::disk('public')->put("uploads/overtimes/$overtime->id/attendance/clock_in/$pictureName", $imageData);
                $picture_path = env('APP_URL') . "/storage/uploads/overtimes/$overtime->id/attendance/clock_in/$pictureName";
            }

            $overtime->clock_in   = date('Y-m-d H:i:s');
            $overtime->coord_in   = "$request->latitude, $request->longitude, $request->accuracy";
            $overtime->picture_in = $picture_path;
            $overtime->save();

            return redirect()->back()->with('success', __('Attendance Successfully Added'));
        } else {
            return redirect()->back()->with('error', __('Failed Adding Attendance'));
        }
    }

    public function report(Request $request)
    {
        $overtime = Overtime::find($request->overtimeId);
        if ($overtime) {
            $document_path = null;
            $employee = Employee::where('is_active')->where('user_id', Auth::user()->id)->first();

            if (empty($employee) || !$employee) {
                return redirect()->back()->with('error', __('Inactive'));
            }

            if ($overtime->report_document && $request->file('myDocument')) {
                $filepath_array = explode('/', $overtime->report_document);
                $filename = array_pop($filepath_array);

                // Check if the file exists before attempting to delete
                if (Storage::disk('public')->exists("uploads/overtimes/$overtime->id/report/$filename")) {
                    Storage::disk('public')->delete("uploads/overtimes/$overtime->id/report/$filename");
                }
            }

            if ($request->file('myDocument')) {
                $docs = $request->file('myDocument');
                $docName = time() . "_" . date('Y-m-d') . "_" . preg_replace('/\s+/', '', $employee->name) . "." . $docs->getClientOriginalExtension();
                $path = $docs->storeAs("uploads/overtimes/$overtime->id/report", $docName, 'public');
                $document_path = env('APP_URL') . '/storage/' . $path;
            }

            $overtime->report_note     = $request->note;
            $overtime->report_document = $document_path;
            $overtime->save();
            return redirect()->back()->with('success', __('Report Successfully Added'));
        } else {
            return redirect()->back()->with('error', __('Failed Adding Report'));
        }
    }
}
