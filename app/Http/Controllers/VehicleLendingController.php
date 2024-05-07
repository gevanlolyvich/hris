<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Vehicle;
use App\Models\VehicleLending;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use File;

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
     */
    public function show(VehicleLending $vehicleLending)
    {
        return view('vehicle-lending.show', compact('vehicleLending'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\VehicleLending  $vehicleLending
     */
    public function edit(VehicleLending $vehicleLending)
    {
        $unavailable_vehicle_id = VehicleLending::where('date', $vehicleLending->date)->whereNot('id', $vehicleLending->id)->where('status', 'Approved')->select('vehicle_id')->get()->pluck('name', 'id');
        if (\Auth::user()->vehicleOfficer) {
            if (\Auth::user()->vehicleOfficer->is_resricted) {
                $branch_ids = \Auth::user()->vehicleOfficer->accesses?->pluck('branch_id') ?? [];
                $vehicles   = Vehicle::whereNotIn('id', $unavailable_vehicle_id)->whereIn('branch_id', $branch_ids)->get()->pluck('id');
            } else {
                $vehicles   = Vehicle::whereNotIn('id', $unavailable_vehicle_id)->get();
            }

            foreach ($vehicles as $vehicle) {
                $branch         = $vehicle?->branch?->name ?? '-';
                $vehicle->name  = "{$vehicle->name} | {$vehicle->type} | {$vehicle->police_no} | {$branch}";
            }
            $vehicles           = $vehicles->pluck('name', 'id');

            return view('vehicle-lending.edit', compact('vehicles', 'vehicleLending'));
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

            $vehicles       = $branch_id?->isNotEmpty() ?
                                Vehicle::whereNotIn('id', $unavailable_vehicle_id)->whereIn('branch_id', $branch_id)->get() :
                                Vehicle::whereNotIn('id', $unavailable_vehicle_id)->get();
            foreach ($vehicles as $vehicle) {
                $branch         = $vehicle?->branch?->name ?? '-';
                $vehicle->name  = "{$vehicle->name} | {$vehicle->type} | {$vehicle->police_no} | {$branch}";
            }
            $vehicles           = $vehicles->pluck('name', 'id');

            return view('vehicle-lending.edit', compact('vehicles', 'vehicleLending'));
        } else {
            $vehicles   = Vehicle::whereNotIn('id', $unavailable_vehicle_id)->get();

            foreach ($vehicles as $vehicle) {
                $branch         = $vehicle?->branch?->name ?? '-';
                $vehicle->name  = "{$vehicle->name} | {$vehicle->type} | {$vehicle->police_no} | {$branch}";
            }
            $vehicles           = $vehicles->pluck('name', 'id');

            return view('vehicle-lending.edit', compact('vehicles', 'vehicleLending'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\VehicleLending  $vehicleLending
     */
    public function update(Request $request, VehicleLending $vehicleLending)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'vehicle_id' => 'required',
                'date' => "required|date|after_or_equal:{$vehicleLending->date}",
                'purpose' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        // Create New Vehicle Officer
        $vehicleLending->vehicle_id    = $request->vehicle_id;
        $vehicleLending->date          = $request->date;
        $vehicleLending->purpose       = $request->purpose;
        $vehicleLending->save();

        return redirect()->route('vehicle-lending.index')->with('success', __('Vehicle Lending Successfully Updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VehicleLending  $vehicleLending
     */
    public function destroy(VehicleLending $vehicleLending)
    {
        if (\Auth::user()->vehicleOfficer || \Auth::user()->type != 'employee' || $vehicleLending->request_by == \Auth::user()->id) {
            $vehicleLending->delete();

            return redirect()->route('vehicle-lending.index')->with('success', __('Vehicle Lending Successfully Deleted'));
        } else {
            return redirect()->route('vehicle-lending.index')->with('error', __('Permission denied.'));
        }
    }

    public function approval(Request $request)
    {
        if (\Auth::user()->vehicleOfficer || \Auth::user()->type != 'employee') {
            $lending                    = VehicleLending::find($request->lending_id);
            if ($lending) {

                // Check Vehicle Availability
                $unavailable_vehicle_id = VehicleLending::where('date', $lending->date)->where('status', 'Approved')->select('vehicle_id')->get()->pluck('vehicle_id')->toArray();

                if (!in_array($lending->vehicle_id, $unavailable_vehicle_id)) {
                    // Update Lending Status Data
                    $lending->status        = $request->status;
                    $lending->approved_by   = \Auth::user()->id;
                    $lending->save();
                } else {
                    return redirect()->route('vehicle-lending.index')->with('error', __('Vehicle Unavailable'));
                }
            }

            return redirect()->route('vehicle-lending.index')->with('success', __('Vehicle Lending Status Successfully Updated'));
        } else {
            return redirect()->route('vehicle-lending.index')->with('error', __('Permission denied.'));
        }
    }

    public function getProof($lending_id)
    {
        $vehicleLending = VehicleLending::find($lending_id);
        if ($vehicleLending) {
            return view('vehicle-lending.proof', compact('vehicleLending'));
        } else {
            return redirect()->route('vehicle-lending.index')->with('error', __('Permission denied.'));
        }
    }

    public function proof(Request $request) {
        $vehicleLending = VehicleLending::find($request->lending_id);
        if ($vehicleLending) {
            $name                   = $vehicleLending->requester?->name ?? ' ';
            $emp_name               = preg_replace('/\s+/', '', $name);
            $pickup_document_path   = null;
            $return_document_path   = null;

            // Preaparing File From Request;
            if ($request->hasFile('pickup_file')) {
                $docs                   = $request->pickup_file;
                $pickup_docName         = time() . "_" . date('Y-m-d') . "_" . $emp_name . '_pickup'  . "." . $docs->getClientOriginalExtension();
                $pickup_path            = $docs->storeAs("uploads/vehicle_lendings/{$request->lending_id}/" . $emp_name, $pickup_docName, 'public');
                $pickup_document_path   = env('APP_URL') . '/storage/' . $pickup_path;

                // Delete Old file
                $old_pickup_file_path = str_replace(env('APP_URL') . '/storage', '../storage/app/public', $vehicleLending->pickup_file);
                if (File::exists($old_pickup_file_path)) {
                    File::delete($old_pickup_file_path);
                }
            }
            if ($request->hasFile('return_file')) {
                $docs                   = $request->return_file;
                $return_docName         = time() . "_" . date('Y-m-d') . "_" . $emp_name . '_return'  . "." . $docs->getClientOriginalExtension();
                $return_path            = $docs->storeAs("uploads/vehicle_lendings/{$request->lending_id}/" . $emp_name, $return_docName, 'public');
                $return_document_path   = env('APP_URL') . '/storage/' . $return_path;

                // Delete Old file
                $old_return_file_path = str_replace(env('APP_URL') . '/storage', '../storage/app/public', $vehicleLending->return_file);
                if (File::exists($old_return_file_path)) {
                    File::delete($old_return_file_path);
                }
            }

            $vehicleLending->pickup_km      = $request->pickup_km ? $request->pickup_km : $vehicleLending->pickup_km;
            $vehicleLending->pickup_time    = $request->pickup_time ? $request->pickup_time : $vehicleLending->pickup_time;
            $vehicleLending->pickup_file    = $pickup_document_path ? $pickup_document_path : $vehicleLending->pickup_file;
            $vehicleLending->return_km      = $request->return_km ? $request->return_km : $vehicleLending->return_km;
            $vehicleLending->return_time    = $request->return_time ? $request->return_time : $vehicleLending->return_time;
            $vehicleLending->return_file    = $return_document_path ? $return_document_path : $vehicleLending->return_file;
            $vehicleLending->save();

            return redirect()->route('vehicle-lending.index')->with('success', __('Vehicle Lending Status Successfully Updated'));
        } else {
            return redirect()->route('vehicle-lending.index')->with('error', __('Permission denied.'));
        }
    }

    public function getVehicleAvailabilityByDate(Request $request) {
        $lendings       = VehicleLending::where('date', $request->date)->where('status', 'Approved')->select('vehicle_id')->get()->pluck('vehicle_id');

        $vehicles       = Vehicle::whereNotIn('id', $lendings)->get();
        foreach ($vehicles as $vehicle) {
            $branch         = $vehicle?->branch?->name ?? '-';
            $vehicle->name  = "{$vehicle->name} | {$vehicle->type} | {$vehicle->police_no} | {$branch}";
        }
        $vehicles       = $vehicles->pluck('name', 'id');

        return $vehicles;
    }
}
