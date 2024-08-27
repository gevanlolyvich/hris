<?php

namespace App\Http\Controllers;

use File;
use App\Exports\TrainingExport;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Department;
use App\Models\PushSubscription;
use App\Models\Trainer;
use App\Models\Training;
use App\Models\TrainingType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class TrainingController extends Controller {

    public function index() {
        if(\Auth::user()->can('Manage Training')) {
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

            if(\Auth::user()->type == 'employee')
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

                    $trainings = Training::whereIn('employee', $employees);

                } else {
                    $trainings = Training::where('employee', $emp);
                }
            }
            else
            {
                $employee = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->select('id') : Employee::select('id');
                
                $employee = $employee?->orderby('name', 'asc')?->get()?->pluck('id');

                $trainings = Training::whereIn('employee', $employee);
            }

            $trainings = $trainings->orderBy('created_at', 'desc')->get();

            return view('training.index', compact('trainings'));
        }
        else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create() {
        if(\Auth::user()->can('Create Training')) {
            if (\Auth::user()->type == 'employee') {
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

            $trainingTypes = TrainingType::orderBy('name', 'asc')->get()->pluck('name', 'id');

            return view('training.create', compact('trainingTypes', 'employees'));
        }
        else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function store(Request $request) {
        if(\Auth::user()->can('Create Training')) {
            $validator = \Validator::make(
                $request->all(), [
                    'employee' => 'required',
                    'name' => 'required',
                    'organizer' => 'required',
                    'training_type' => 'required',
                    'training_cost' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required',
                ]
            );
            if($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $training                   = new Training();
            $training->name             = $request->name;
            $training->organizer        = $request->organizer;
            $training->training_type    = $request->training_type;
            $training->training_cost    = $request->training_cost;
            $training->employee         = $request->employee;
            $training->start_date       = $request->start_date;
            $training->end_date         = $request->end_date;
            $training->description      = $request->description;
            $training->created_by       = \Auth::user()->id;
            $training->save();

            // Send Notification To HR
            $subscriptions = [];
            $employee = Employee::where('is_active', 1)->find($request->employee);

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
                    'title' => __('New training Request'),
                    'body' => $employee->name . ' ' . __('Make Training Request') . ' [' . $training->name . '] ' . __('For Date') . ' ' . $request->start_date . ' ' . __('To Date') . ' ' . $request->end_date,
                    'url' => "/training"
                ]),
                'high'
            );

            return redirect()->route('training.index')->with('success', __('Training successfully created.'));
        }
        else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show($id) {
        $training    = Training::find($id);

        if (empty($training)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        return view('training.show', compact('training'));
    }

    public function edit(Training $training) {
        if(\Auth::user()->can('Create Training')) {
            if (\Auth::user()->type == 'employee') {
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

            $trainingTypes = TrainingType::orderBy('name', 'asc')->get()->pluck('name', 'id');

            return view('training.edit', compact('trainingTypes', 'training', 'employees'));
        }
        else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, Training $training) {
        if(\Auth::user()->can('Edit Training')) {

            $validator = \Validator::make(
                $request->all(), [
                    'employee' => 'required',
                    'name' => 'required',
                    'organizer' => 'required',
                    'training_type' => 'required',
                    'training_cost' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required',
                ]
            );
            if($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $training->name             = $request->name;
            $training->organizer        = $request->organizer;
            $training->training_type    = $request->training_type;
            $training->training_cost    = $request->training_cost;
            $training->employee         = $request->employee;
            $training->start_date       = $request->start_date;
            $training->end_date         = $request->end_date;
            $training->description      = $request->description;
            $training->save();

            return redirect()->route('training.index')->with('success', __('Training successfully updated.'));
        }
        else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(Training $training)
    {
        if(\Auth::user()->can('Delete Training')) {
            if($training->created_by == \Auth::user()->id || \Auth::user()->type != 'employee') {
                $training->delete();

                return redirect()->route('training.index')->with('success', __('Training successfully deleted.'));
            }
            else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function updateStatus(Request $request) {
        $training              = Training::find($request->id);
        $training->performance = $request->performance;
        $training->status      = $request->status;
        $training->remarks     = $request->remarks;
        $training->save();

        return redirect()->route('training.index')->with('success', __('Training status successfully updated.'));
    }

    public function export() {
        $name = 'training_' . date('Y-m-d i:h:s');
        $data = Excel::download(new TrainingExport(), $name . '.xlsx');
        
        return $data;
    }

    public function approval(Request $request)
    {
        if (\Auth::user()->type != 'employee') {
            $training               = Training::find($request->training_id);
            $training->status       = $request->status;
            $training->approved_by  = \Auth::user()->id;
            $training->save();

            // Send push notification to employee
            $subscriptions = [];
            if ($training?->employee_ref?->user?->pushNotifications) {
                foreach ($training->employee_ref->user->pushNotifications ?? [] as $sub) {
                    array_push($subscriptions, ['data' => $sub->data, 'name' => $training->employee_ref->user->name]);
                }
            }

            $status = $request->status == 'Approved' ? 'Approved' : 'Rejected';
            \Auth::user()->sendNotifications(
                $subscriptions,
                json_encode([
                    'title' => __('Training Request') . ' ' . __($status),
                    'body' => __('Training Request') . ' ' . $training->name . ' '. __('For Date') . ' ' . $training->start_date . ' ' . __('To Date') . ' ' . $training->end_date . ' '. __($status),
                    'url' => '/training'
                ]),
                'normal'
            );

            return redirect()->back()->with('success', __('Training status successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getResult($training_id) {
        $training = Training::find($training_id);
        if ($training && (\Auth::user()?->employee->id == $training->employee || \Auth::user()->type != 'employee')) {
            return view('training.result', compact('training'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function uploadResult(Request $request) {
        $training               = Training::find($request->training_id);
        if (\Auth::user()?->employee->id == $training?->employee && $training) {
            $name                   = $training?->employee_ref?->user?->name ?? '-';
            $emp_name               = preg_replace('/\s+/', '', $name);

            // Preaparing File From Request;
            if ($request->hasFile('result_file')) {
                $file          = $request->result_file;
                $filename       = time() . "_" . date('Y-m-d') . "_" . $emp_name . '_pickup_1'  . "." . $file->getClientOriginalExtension();
                $filepath          = $file->storeAs("uploads/trainings/{$request->training_id}/{$emp_name}", $filename, 'public');
                $result_file_path = env('APP_URL') . '/storage/' . $filepath;

                // Delete Old file
                $old_file = str_replace(env('APP_URL') . '/storage', '../storage/app/public', $training->result_file);
                if (File::exists($old_file)) {
                    File::delete($old_file);
                }
            }

            $training->result_file = $result_file_path ?? $training->result_file;
            $training->save();

            return redirect()->back()->with('success', __('Training status successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
