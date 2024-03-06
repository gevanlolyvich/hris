<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\LevelDesignation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LevelDesignationController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('Manage Level')) {

            $levels     = LevelDesignation::get();

            return view('level-designation.index', compact('levels'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Level')) {
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

            $designations       = Designation::orderBy('name', 'ASC');

            if ($branch_id?->isNotEmpty()) {
                $branch         = Branch::whereIn('id', $branch_id)->select('id')->get()->pluck('id');
                $designations   = $designations->whereIn('branch_id', $branch);
            }

            $designations       = $designations->get();
            foreach ($designations as $designation) {
                $department     = Department::find($designation?->department_id);
                $branch         = Branch::select('name')->find($department?->branch_id);

                $designation->name = $branch->name. ' | ' . $department->name . ' | ' . $designation->name;
            }
            $designations       = $designations->pluck('name', 'id');

            return view('level-designation.create', compact('branch', 'designations'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Level')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'designation_id' => 'required',
                    'can_self_assessment' => 'required|boolean',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $alreadyAssignDesignation   = Designation::whereIn('id', $request->designation_id)->whereNotNull('level_id')->get()->pluck('name');
            if (count($alreadyAssignDesignation) > 0) {
                return redirect()->back()->with('error',  implode(',', $alreadyAssignDesignation->toArray()) . ' ' . __('Designations Already Have A Level'));
            }

            $level                      = new LevelDesignation();
            $level->name                = $request->name;
            $level->designation_ids     = implode(',', $request->designation_id);
            $level->goal_weight         = $request->goal_weight;
            $level->competency_weight   = $request->competency_weight;
            $level->can_self_assessment = $request->can_self_assessment;
            $level->save();

            Designation::whereIn('id', $request->designation_id)->update(['level_id' => $level->id]);

            return redirect()->route('level-designation.index')->with('success', __('New Level Designation Successfully Created'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show($id)
    {
        $level          = LevelDesignation::find($id);
        $designations   = Designation::whereIn('id', explode(',', $level->designation_ids))->get();
        foreach ($designations as $designation) {
            $department     = Department::find($designation?->department_id);
            $branch         = Branch::select('name')->find($department?->branch_id);

            $designation->name = $branch->name. ' | ' . $department->name . ' | ' . $designation->name;
        }
        $designations       = $designations->pluck('name')->toArray();
        sort($designations);
        
        return view('level-designation.show', compact('level', 'designations'));
    }

    public function edit($levelDesignation)
    {
        if (\Auth::user()->can('Edit Level')) {
            $level          = LevelDesignation::find($levelDesignation);
            $designations   = Designation::get();
            foreach ($designations as $designation) {
                $department     = Department::find($designation?->department_id);
                $branch         = Branch::select('name')->find($department?->branch_id);

                $designation->name = $branch->name. ' | ' . $department->name . ' | ' . $designation->name;
            }
            $designations       = $designations->sortByDesc('name')->pluck('name', 'id')->toArray();

            return view('level-designation.edit', compact('level', 'designations'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, LevelDesignation $levelDesignation)
    {
        if (\Auth::user()->can('Edit Level')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'designation_id' => 'required',
                    'can_self_assessment' => 'required|boolean',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $old_designation_ids                    = explode(',', $levelDesignation->designation_ids);
            $difference_to_delete                   = array_diff($old_designation_ids, $request->designation_id);
            $difference_to_create                   = array_diff($request->designation_id, $old_designation_ids);

            $levelDesignation->name                 = $request->name;
            $levelDesignation->can_self_assessment  = $request->can_self_assessment;
            $levelDesignation->designation_ids      = implode(',', $request->designation_id);
            $levelDesignation->goal_weight          = $request->goal_weight;
            $levelDesignation->competency_weight    = $request->competency_weight;
            $levelDesignation->save();

            // update desgination level_id
            Designation::whereIn('id', $difference_to_delete)->update(['level_id' => null]);
            Designation::whereIn('id', $difference_to_create)->update(['level_id' => $levelDesignation->id]);
            return redirect()->back()->with('success', __('Level Designation Succesfully Updated'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function destroy(LevelDesignation $levelDesignation)
    {
        if (\Auth::user()->can('Delete Level')) {
            $levelDesignation->delete();

            Designation::whereIn('id', explode(',', $levelDesignation->designation_ids))->update(['level_id' => null]);
            return redirect()->back()->with('success', __('Level Designation Succesfully Deleted'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
