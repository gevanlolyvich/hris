<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Vehicle;
use App\Models\VehicleLending;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VehicleLendingController extends Controller
{
    public function index(Request $request)
    {
        if (\Auth::user()->vehicleOfficer) {
            if (\Auth::user()->vehicleOfficer->is_resricted) {
                $branch_ids = \Auth::user()->vehicleOfficer->accesses?->pluck('branch_id') ?? [];
                $branch     = Branch::whereIn('id', $branch_ids)->select('id', 'name')->get()->pluck('name', 'id');
                $vehicles   = Vehicle::whereIn('branch_id', $branch_ids)->get()->pluck('id');
            } else {
                $branch     = Branch::select('id', 'name')->get()->pluck('name', 'id');
                $vehicles   = Vehicle::get()->pluck('id');
            }

            $lendings       = VehicleLending::whereIn('vehicle_id', $vehicles)->orderby('date', 'DESC');
            if ($request->type == 'monthly' && !empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));
    
                $start_date = date($year . '-' . $month . '-01');
                $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
    
                // old date
                // $end_date   = date($year . '-' . $month . '-t');
    
                $lendings->whereBetween(
                    'date',
                    [
                        $start_date,
                        $end_date,
                    ]
                );
            } elseif ($request->type == 'daily' && !empty($request->date)) {
                $lendings->where('date', $request->date);
            } else {
                $month      = date('m');
                $year       = date('Y');
                $start_date = date($year . '-' . $month . '-01');
                $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
    
                // old date
                // $end_date   = date($year . '-' . $month . '-t');
    
                $lendings->whereBetween(
                    'date',
                    [
                        $start_date,
                        $end_date,
                    ]
                );
            }
            $lendings   = $lendings->get();

            return view('vehicle-lending.index', compact('lendings', 'branch'));
        } else if (\Auth::user()->type != 'employee') {
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

            $branch         = $branch_id?->isNotEmpty() ? Branch::whereIn('id', $branch_id)->select('id', 'name')->get()->pluck('name', 'id') : Branch::select('id', 'name')->get()->pluck('name', 'id');
            $vehicles       = $branch_id?->isNotEmpty() ? Vehicle::whereIn('branch_id', $branch_id)->get()->pluck('id') : Vehicle::get()->pluck('id');
            $lendings       = VehicleLending::whereIn('vehicle_id', $vehicles)->orderby('date', 'DESC');

            if ($request->type == 'monthly' && !empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));
    
                $start_date = date($year . '-' . $month . '-01');
                $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
    
                // old date
                // $end_date   = date($year . '-' . $month . '-t');
    
                $lendings->whereBetween(
                    'date',
                    [
                        $start_date,
                        $end_date,
                    ]
                );
            } elseif ($request->type == 'daily' && !empty($request->date)) {
                $lendings->where('date', $request->date);
            } else {
                $month      = date('m');
                $year       = date('Y');
                $start_date = date($year . '-' . $month . '-01');
                $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
    
                // old date
                // $end_date   = date($year . '-' . $month . '-t');
    
                $lendings->whereBetween(
                    'date',
                    [
                        $start_date,
                        $end_date,
                    ]
                );
            }

            $lendings   = $lendings->get();

            return view('vehicle-lending.index', compact('lendings', 'branch'));
        } else {
            $lendings = VehicleLending::where('request_by', \Auth::user()->id)->orderby('date', 'DESC')->get();
            
            return view('vehicle-lending.index', compact('lendings'));
        }
    }

    public function create()
    {
        if (\Auth::user()->vehicleOfficer) {
            if (\Auth::user()->vehicleOfficer->is_resricted) {
                $branch_ids = \Auth::user()->vehicleOfficer->accesses?->pluck('branch_id') ?? [];
                $vehicles   = Vehicle::whereIn('branch_id', $branch_ids)->get()->pluck('id');
            } else {
                $vehicles   = Vehicle::get();
            }

            foreach ($vehicles as $vehicle) {
                $branch         = $vehicle?->branch?->name ?? '-';
                $vehicle->name  = "{$vehicle->name} | {$vehicle->type} | {$vehicle->police_no} | {$branch}";
            }
            $vehicles           = $vehicles->pluck('name', 'id');

            return view('vehicle-lending.create', compact('vehicles'));
        } else if (\Auth::user()->type != 'employee') {
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

            $vehicles       = $branch_id?->isNotEmpty() ? Vehicle::whereIn('branch_id', $branch_id)->get() : Vehicle::get();
            foreach ($vehicles as $vehicle) {
                $branch         = $vehicle?->branch?->name ?? '-';
                $vehicle->name  = "{$vehicle->name} | {$vehicle->type} | {$vehicle->police_no} | {$branch}";
            }
            $vehicles           = $vehicles->pluck('name', 'id');

            return view('vehicle-lending.create', compact('vehicles'));
        } else {
            $vehicles   = Vehicle::get();

            foreach ($vehicles as $vehicle) {
                $branch         = $vehicle?->branch?->name ?? '-';
                $vehicle->name  = "{$vehicle->name} | {$vehicle->type} | {$vehicle->police_no} | {$branch}";
            }
            $vehicles           = $vehicles->pluck('name', 'id');

            return view('vehicle-lending.create', compact('vehicles'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'vehicle_id' => 'required',
                'date' => 'required|date|after_or_equal:today',
                'purpose' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        // Create New Vehicle Officer
        $lending                = new VehicleLending();
        $lending->request_by    = \Auth::user()->id;
        $lending->vehicle_id    = $request->vehicle_id;
        $lending->date          = $request->date;
        $lending->purpose       = $request->purpose;
        $lending->save();

        return redirect()->route('vehicle-lending.index')->with('success', __('Vehicle Lending Successfully Created'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\VehicleLending  $vehicleLending
     * @return \Illuminate\Http\Response
     */
    public function show(VehicleLending $vehicleLending)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\VehicleLending  $vehicleLending
     * @return \Illuminate\Http\Response
     */
    public function edit(VehicleLending $vehicleLending)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\VehicleLending  $vehicleLending
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VehicleLending $vehicleLending)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VehicleLending  $vehicleLending
     * @return \Illuminate\Http\Response
     */
    public function destroy(VehicleLending $vehicleLending)
    {
        //
    }
}
