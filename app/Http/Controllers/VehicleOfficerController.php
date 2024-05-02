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

            $officers     = VehicleOfficer::get();

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
        Log::info($vehicleOfficer);

        if (\Auth::user()->type != 'employee') {

            return view('vehicle-officer.show', compact('vehicleOfficer'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\VehicleOfficer  $vehicleOfficer
     * @return \Illuminate\Http\Response
     */
    public function edit(VehicleOfficer $vehicleOfficer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\VehicleOfficer  $vehicleOfficer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VehicleOfficer $vehicleOfficer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VehicleOfficer  $vehicleOfficer
     * @return \Illuminate\Http\Response
     */
    public function destroy(VehicleOfficer $vehicleOfficer)
    {
        //
    }
}
