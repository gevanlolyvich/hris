<?php

namespace App\Http\Controllers;

use App\Exports\TrainingExport;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Trainer;
use App\Models\Training;
use App\Models\TrainingType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
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

            $trainings = $branch_id?->isNotEmpty() ? Training::whereIn('branch', $branch_id)->get() : Training::get();
            $status    = Training::$Status;

            return view('training.index', compact('trainings', 'status'));
        }
        else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create() {
        if(\Auth::user()->can('Create Training')) {
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

            $branches      = $branch_id?->isNotEmpty() ? Branch::whereIn('id', $branch_id)->get()->pluck('name', 'id') : Branch::get()->pluck('name', 'id');
            $trainingTypes = TrainingType::get()->pluck('name', 'id');
            $trainers      = $branch_id?->isNotEmpty() ? Trainer::whereIn('branch', $branch_id)->get()->pluck('firstname', 'id') : Trainer::get()->pluck('firstname', 'id');
            $employees     = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id') : Employee::where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id');
            $options       = Training::$options;

            return view('training.create', compact('branches', 'trainingTypes', 'trainers', 'employees', 'options'));
        }
        else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function store(Request $request) {
        if(\Auth::user()->can('Create Training')) {

            $validator = \Validator::make(
                $request->all(), [
                                   'branch' => 'required',
                                   'training_type' => 'required',
                                   'training_cost' => 'required',
                                   'employee' => 'required',
                                   'start_date' => 'required',
                                   'end_date' => 'required',
                               ]
            );
            if($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $training                 = new Training();
            $training->branch         = $request->branch;
            $training->trainer_option = $request->trainer_option;
            $training->training_type  = $request->training_type;
            $training->trainer        = $request->trainer;
            $training->training_cost  = $request->training_cost;
            $training->employee       = $request->employee;
            $training->start_date     = $request->start_date;
            $training->end_date       = $request->end_date;
            $training->description    = $request->description;
            $training->created_by     = \Auth::user()->id;
            $training->save();

            return redirect()->route('training.index')->with('success', __('Training successfully created.'));
        }
        else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show($id) {
        $traId       = Crypt::decrypt($id);

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

        $training    = $branch_id?->isNotEmpty() ? Training::whereIn('branch', $branch_id)->find($traId) : Training::find($traId);

        if (empty($training)) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $performance = Training::$performance;
        $status      = Training::$Status;

        return view('training.show', compact('training', 'performance', 'status'));
    }


    public function edit(Training $training) {
        if(\Auth::user()->can('Create Training')) {
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

            $branches      = $branch_id?->isNotEmpty() ? Branch::whereIn('id', $branch_id)->get()->pluck('name', 'id') : Branch::get()->pluck('name', 'id');
            $trainingTypes = TrainingType::get()->pluck('name', 'id');
            $trainers      = $branch_id?->isNotEmpty() ? Trainer::whereIn('branch', $branch_id)->get()->pluck('firstname', 'id') : Trainer::get()->pluck('firstname', 'id');
            $employees     = $branch_id?->isNotEmpty() ? Employee::whereIn('branch_id', $branch_id)->where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id') : Employee::where('is_active', 1)->orderby('name', 'asc')->get()->pluck('name', 'id');
            $options       = Training::$options;

            return view('training.edit', compact('branches', 'trainingTypes', 'trainers', 'employees', 'options', 'training'));
        }
        else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function update(Request $request, Training $training) {
        if(\Auth::user()->can('Edit Training')) {

            $validator = \Validator::make(
                $request->all(), [
                                   'branch' => 'required',
                                   'training_type' => 'required',
                                   'training_cost' => 'required',
                                   'employee' => 'required',
                                   'start_date' => 'required',
                                   'end_date' => 'required',
                               ]
            );
            if($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $training->branch         = $request->branch;
            $training->trainer_option = $request->trainer_option;
            $training->training_type  = $request->training_type;
            $training->trainer        = $request->trainer;
            $training->training_cost  = $request->training_cost;
            $training->employee       = $request->employee;
            $training->start_date     = $request->start_date;
            $training->end_date       = $request->end_date;
            $training->description    = $request->description;
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
}
