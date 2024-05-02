<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Designation;
use App\Models\Department;
use App\Models\User;
use App\Models\VehicleOfficer;
use App\Models\VehicleOfficerAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VehicleOfficerController extends Controller
{
    public function index()
    {
        if (\Auth::user()->type != 'employee') {
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

            if ($branch_id?->isNotEmpty()) {
                $users          = User::whereIn('branch_id', $branch_id)->select('id')->get()->pluck('id');
                $officers     = VehicleOfficer::whereIn('user_id', $users)->get();
            } else {
                $officers     = VehicleOfficer::get();
            }


            return view('vehicle-officer.index', compact('officers'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->type != 'employee') {
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

            $existedOfficer     = VehicleOfficer::select('user_id')->get()->pluck('user_id');

            if ($branch_id?->isNotEmpty()) {
                $branches       = Branch::whereIn('id', $branch_id)->select('id', 'name')->get()->pluck('name', 'id');
                $users          = User::whereNotIn('id', $existedOfficer)->whereIn('branch_id', $branch_id)->select('id', 'name', 'branch_id', 'type')->get();
                $full_access    = false;
            } else {
                $branches       = Branch::select('id', 'name')->get()->pluck('name', 'id');
                $users          = User::whereNotIn('id', $existedOfficer)->select('id', 'name', 'branch_id', 'type')->get();
                $full_access    = true;
            }

            foreach ($users as $user) {
                $branch     = $user?->branch?->name ?? '-';
                $user->name = "{$user->name} | {$user->type} | {$branch}";
            }
            $users          = $users->pluck('name', 'id');

            $access         = [0 => __('Full Access'), 1 => __('Resricted Access')];

            return view('vehicle-officer.create', compact('branches', 'users', 'full_access', 'access'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->type != 'employee') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'user' => 'required',
                    'is_resricted' => 'required',
                    'branch_id' => 'required_if:is_resricted,==,1|array',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            // Create New Vehicle Officer
            $officer                = new VehicleOfficer();
            $officer->user_id       = $request->user;
            $officer->is_resricted  = $request->is_resricted;
            $officer->created_by    = \Auth::user()->id;
            $officer->save();

            // Create Access For New Officer if access is resricted
            if ($request->is_resricted == 1) {
                foreach($request->branch_id as $branch) {
                    VehicleOfficerAccess::create([
                        'officer_id' => $officer->id,
                        'branch_id'  => $branch,
                    ]);
                }
            }

            return redirect()->route('vehicle-officer.index')->with('success', __('Vehicle Officer Successfully Created'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(VehicleOfficer $vehicleOfficer)
    {
        if (\Auth::user()->type != 'employee') {

            return view('vehicle-officer.show', compact('vehicleOfficer'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit(VehicleOfficer $vehicleOfficer)
    {
        if (\Auth::user()->type != 'employee') {
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

            $existedOfficer     = VehicleOfficer::whereNot('id', $vehicleOfficer->id)->select('user_id')->get()->pluck('user_id');

            if ($branch_id?->isNotEmpty()) {
                $branches       = Branch::whereIn('id', $branch_id)->select('id', 'name')->get()->pluck('name', 'id');
                $users          = User::whereNotIn('id', $existedOfficer)->whereIn('branch_id', $branch_id)->select('id', 'name', 'branch_id', 'type')->get();
                $full_access    = false;
            } else {
                $branches       = Branch::select('id', 'name')->get()->pluck('name', 'id');
                $users          = User::whereNotIn('id', $existedOfficer)->select('id', 'name', 'branch_id', 'type')->get();
                $full_access    = true;
            }

            foreach ($users as $user) {
                $branch         = $user?->branch?->name ?? '-';
                $user->name     = "{$user->name} | {$user->type} | {$branch}";
            }
            $users              = $users->pluck('name', 'id');
            
            $access             = [0 => __('Full Access'), 1 => __('Resricted Access')];
            $choosenBranches   = $vehicleOfficer?->accesses?->pluck('branch_id') ?? [];
            return view('vehicle-officer.edit', compact('vehicleOfficer', 'users', 'full_access', 'access', 'branches', 'choosenBranches'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, VehicleOfficer $vehicleOfficer)
    {
        if (\Auth::user()->type != 'employee') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'user' => 'required',
                    'is_resricted' => 'required',
                    'branch_id' => 'required_if:is_resricted,==,1|array',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            // Update Vehicle Officer
            $officer                = $vehicleOfficer;
            $officer->user_id       = $request->user;
            $officer->is_resricted  = $request->is_resricted;
            $officer->save();

            // Create or Delete Access For Officer if access is resricted
            if ($request->is_resricted == 1) {
                // Preaparing Data
                $old_data               = VehicleOfficerAccess::where('officer_id', $officer->id)->select('branch_id')->get()->pluck('branch_id')->toArray();
                $difference_to_delete   = array_diff($old_data, $request->branch_id);
                $difference_to_create   = array_diff($request->branch_id, $old_data);

                // Creating New Access
                foreach($difference_to_create as $branch) {
                    VehicleOfficerAccess::create([
                        'officer_id' => $officer->id,
                        'branch_id'  => $branch,
                    ]);
                }

                // Delete Unused Access
                VehicleOfficerAccess::where('officer_id', $officer->id)->whereIn('branch_id', $difference_to_delete)->delete();
            } else {
                VehicleOfficerAccess::where('officer_id', $officer->id)->delete();
            }

            return redirect()->route('vehicle-officer.index')->with('success', __('Vehicle Officer Successfully Updated'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VehicleOfficer  $vehicleOfficer
     */
    public function destroy(VehicleOfficer $vehicleOfficer)
    {
        if (\Auth::user()->type != 'employee') {

            $vehicleOfficer->delete();

            VehicleOfficerAccess::where('officer_id', $vehicleOfficer->id)->delete();
            return redirect()->route('vehicle-officer.index')->with('success', __('Vehicle Officer Successfully Deleted'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
