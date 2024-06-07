<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VehicleController extends Controller
{
    public function index()
    {
        if (\Auth::user()->vehicleOfficer) {
            if (\Auth::user()->vehicleOfficer->is_resricted) {
                $branch_ids = \Auth::user()->vehicleOfficer->accesses?->pluck('branch_id') ?? [];
                $vehicles   = Vehicle::whereIn('branch_id', $branch_ids)->get();
            } else {
                $vehicles   = Vehicle::get();
            }

            return view('vehicle.index', compact('vehicles'));
        }
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

            $vehicles   = $branch_id?->isNotEmpty() ? Vehicle::whereIn('branch_id', $branch_id)->get() : Vehicle::get();

            return view('vehicle.index', compact('vehicles'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->vehicleOfficer) {
            if (\Auth::user()->vehicleOfficer->is_resricted) {
                $allowed_branches   = \Auth::user()->vehicleOfficer->accesses?->pluck('branch_id') ?? [];
                $branches           = Branch::whereIn('id', $allowed_branches)->select('id', 'name')->get()->pluck('name', 'id');
            } else {
                $branches           = Branch::select('id', 'name')->get()->pluck('name', 'id');
            }

            return view('vehicle.create', compact('branches'));
        } else if (\Auth::user()->type != 'employee') {
            $branch     = Branch::find(\Auth::user()->branch_id);
            $branch_id  = collect();
            if ($branch) {
                $branch_id->push($branch?->id);
            }

            $children   = $branch?->childBranchFlatten();
            if ($children?->isNotEmpty()) {
                foreach ($children as $child) {
                    $branch_id->push($child->id);
                }
            }

            $branches   = $branch_id?->isNotEmpty() ? Branch::whereIn('id', $branch_id)->select('id', 'name')->get()->pluck('name', 'id') : Branch::select('id', 'name')->get()->pluck('name', 'id');

            return view('vehicle.create', compact('branches'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request)
    {
        if (\Auth::user()->vehicleOfficer || \Auth::user()->type != 'employee') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'type' => 'required',
                    'police_no' => 'required',
                    'km' => 'required',
                    'emoney_balance' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            // Create New Vehicle
            $vehicle                    = new Vehicle();
            $vehicle->name              = $request->name;
            $vehicle->type              = strtoupper($request->type);
            $vehicle->police_no         = strtoupper($request->police_no);
            $vehicle->km                = $request->km;
            $vehicle->emoney_balance    = $request->emoney_balance;
            $vehicle->branch_id         = $request->branch_id;
            $vehicle->save();

            return redirect()->route('vehicle.index')->with('success', __('Vehicle Successfully Created'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Vehicle  $vehicle
     */
    public function show(Vehicle $vehicle)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Vehicle  $vehicle
     */
    public function edit(Vehicle $vehicle)
    {
        $status = [0 => __('Inactive'), 1 => __('Active')];
        if (\Auth::user()->vehicleOfficer) {
            if (\Auth::user()->vehicleOfficer->is_resricted) {
                $allowed_branches   = \Auth::user()->vehicleOfficer->accesses?->pluck('branch_id')->toArray() ?? [];
                $branches           = Branch::whereIn('id', $allowed_branches)->select('id', 'name')->get()->pluck('name', 'id');

                if ($vehicle->branch_id && !in_array($vehicle->branch_id, $allowed_branches)) {
                    return redirect()->back()->with('error', __('Permission denied.'));
                }
            } else {
                $branches           = Branch::select('id', 'name')->get()->pluck('name', 'id');
            }

            return view('vehicle.edit', compact('branches', 'vehicle', 'status'));
        } else if (\Auth::user()->type != 'employee') {
            $branch     = Branch::find(\Auth::user()->branch_id);
            $branch_id  = collect();
            if ($branch) {
                $branch_id->push($branch?->id);
            }

            $children   = $branch?->childBranchFlatten();
            if ($children?->isNotEmpty()) {
                foreach ($children as $child) {
                    $branch_id->push($child->id);
                }
            }

            if ($vehicle->branch_id && !in_array($vehicle->branch_id, $branch_id->toArray()) && $branch_id?->isNotEmpty()) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }

            $branches   = $branch_id?->isNotEmpty() ? Branch::whereIn('id', $branch_id)->select('id', 'name')->get()->pluck('name', 'id') : Branch::select('id', 'name')->get()->pluck('name', 'id');

            return view('vehicle.edit', compact('branches', 'vehicle', 'status'));
        } else {
            return redirect()->route('vehicle.index')->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Vehicle  $vehicle
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        Log::info($request);
        if (\Auth::user()->vehicleOfficer || \Auth::user()->type != 'employee') {
            // Access Validity Check
            if (\Auth::user()->vehicleOfficer && \Auth::user()->vehicleOfficer->is_resricted) {
                $allowed_branches   = \Auth::user()->vehicleOfficer->accesses?->pluck('branch_id')->toArray() ?? [];
                $branches           = Branch::whereIn('id', $allowed_branches)->select('id', 'name')->get()->pluck('name', 'id');

                if ($vehicle->branch_id && !in_array($vehicle->branch_id, $allowed_branches)) {
                    return redirect()->back()->with('error', __('Permission denied.'));
                }
    
            } else if (\Auth::user()->type != 'employee') {
                $branch     = Branch::find(\Auth::user()->branch_id);
                $branch_id  = collect();
                if ($branch) {
                    $branch_id->push($branch?->id);
                }
    
                $children   = $branch?->childBranchFlatten();
                if ($children?->isNotEmpty()) {
                    foreach ($children as $child) {
                        $branch_id->push($child->id);
                    }
                }
    
                if ($vehicle->branch_id && !in_array($vehicle->branch_id, $branch_id->toArray()) && $branch_id?->isNotEmpty()) {
                    return redirect()->back()->with('error', __('Permission denied.'));
                }
            }

            // Request Validity Check
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'is_active' => 'required|boolean',
                    'type' => 'required',
                    'police_no' => 'required',
                    'km' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $vehicle->name              = $request->name;
            $vehicle->is_active         = $request->is_active;
            $vehicle->type              = strtoupper($request->type);
            $vehicle->police_no         = strtoupper($request->police_no);
            $vehicle->km                = $request->km;
            $vehicle->emoney_balance    = $request->emoney_balance;
            $vehicle->branch_id         = $request->branch_id;
            $vehicle->save();

            return redirect()->route('vehicle.index')->with('success', __('Vehicle Successfully Updated'));
        } else {
            return redirect()->route('vehicle.index')->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Vehicle  $vehicle
     */
    public function destroy(Vehicle $vehicle)
    {
        if (\Auth::user()->vehicleOfficer || \Auth::user()->type != 'employee') {
            // Access Validity Check
            if (\Auth::user()->vehicleOfficer && \Auth::user()->vehicleOfficer->is_resricted) {
                $allowed_branches   = \Auth::user()->vehicleOfficer->accesses?->pluck('branch_id')->toArray() ?? [];
                $branches           = Branch::whereIn('id', $allowed_branches)->select('id', 'name')->get()->pluck('name', 'id');

                if ($vehicle->branch_id && !in_array($vehicle->branch_id, $allowed_branches)) {
                    return redirect()->back()->with('error', __('Permission denied.'));
                }
    
            } else if (\Auth::user()->type != 'employee') {
                $branch     = Branch::find(\Auth::user()->branch_id);
                $branch_id  = collect();
                if ($branch) {
                    $branch_id->push($branch?->id);
                }
    
                $children   = $branch?->childBranchFlatten();
                if ($children?->isNotEmpty()) {
                    foreach ($children as $child) {
                        $branch_id->push($child->id);
                    }
                }
    
                if ($vehicle->branch_id && !in_array($vehicle->branch_id, $branch_id->toArray()) && $branch_id?->isNotEmpty()) {
                    return redirect()->back()->with('error', __('Permission denied.'));
                }
            }

            $vehicle->delete();

            return redirect()->route('vehicle.index')->with('success', __('Vehicle Successfully Deleted'));
        } else {
            return redirect()->route('vehicle.index')->with('error', __('Permission denied.'));
        }
    }
}
