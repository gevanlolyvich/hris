<?php

namespace App\Http\Controllers;

use File;
use App\Models\Branch;
use App\Models\Vehicle;
use App\Models\VehicleMaintenance;
use App\Models\VehicleMaintenanceType;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VehicleMaintenanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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

            $maintenances       = VehicleMaintenance::whereIn('vehicle_id', $vehicles)->orderby('start_date', 'DESC');
            if ($request->type == 'monthly' && !empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));
    
                $start_date = date($year . '-' . $month . '-01');
                $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                $maintenances->whereBetween(
                    'start_date',
                    [
                        $start_date,
                        $end_date,
                    ]
                );
            } elseif ($request->type == 'daily' && !empty($request->date)) {
                $maintenances->where('start_date', $request->date);
            } else {
                $month      = date('m');
                $year       = date('Y');
                $start_date = date($year . '-' . $month . '-01');
                $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
    
                $maintenances->whereBetween(
                    'start_date',
                    [
                        $start_date,
                        $end_date,
                    ]
                );
            }
            $maintenances   = $maintenances->get();

            return view('vehicle-maintenance.index', compact('maintenances', 'branch'));
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
            $maintenances       = VehicleMaintenance::whereIn('vehicle_id', $vehicles)->orderby('start_date', 'DESC');

            if ($request->type == 'monthly' && !empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));
    
                $start_date = date($year . '-' . $month . '-01');
                $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
    
                // old date
                // $end_date   = date($year . '-' . $month . '-t');
    
                $maintenances->whereBetween(
                    'start_date',
                    [
                        $start_date,
                        $end_date,
                    ]
                );
            } elseif ($request->type == 'daily' && !empty($request->date)) {
                $maintenances->where('start_date', $request->date);
            } else {
                $month      = date('m');
                $year       = date('Y');
                $start_date = date($year . '-' . $month . '-01');
                $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
    
                // old date
                // $end_date   = date($year . '-' . $month . '-t');
    
                $maintenances->whereBetween(
                    'start_date',
                    [
                        $start_date,
                        $end_date,
                    ]
                );
            }

            $maintenances   = $maintenances->get();

            return view('vehicle-maintenance.index', compact('maintenances', 'branch'));
        } else if (\Auth::user()->can('Manage Vehicle Maintenance')) {
            $maintenannces = VehicleMaintenance::orderby('start_date', 'DESC')->get();
            
            return view('vehicle-maintenance.index', compact(var_name: 'maintenannces'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (\Auth::user()->vehicleOfficer) {
            if (\Auth::user()->vehicleOfficer->is_resricted) {
                $branch_ids = \Auth::user()->vehicleOfficer->accesses?->pluck('branch_id') ?? [];
                $vehicles   = Vehicle::whereIn('branch_id', $branch_ids)->get();
            } else {
                $vehicles   = Vehicle::get();
            }

            foreach ($vehicles as $vehicle) {
                $branch         = $vehicle?->branch?->name ?? '-';
                $vehicle->name  = "{$vehicle->name} | {$vehicle->type} | {$vehicle->police_no} | {$branch}";
            }
            $vehicles           = $vehicles->pluck('name', 'id');
            $types              = VehicleMaintenanceType::orderBy('name', 'ASC')->pluck('name', 'id');

            return view('vehicle-maintenance.create', compact('vehicles', 'types'));
        } else if (\Auth::user()->type != 'employee' && \Auth::user()->can('Create Vehicle Maintenance')) {
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

            $vehicles       = $branch_id?->isNotEmpty() ? Vehicle::whereIn('branch_id', $branch_id)->get() : Vehicle::where('is_active', true)->get();
            foreach ($vehicles as $vehicle) {
                $branch         = $vehicle?->branch?->name ?? '-';
                $vehicle->name  = "{$vehicle->name} | {$vehicle->type} | {$vehicle->police_no} | {$branch}";
            }
            $vehicles           = $vehicles->pluck('name', 'id');
            $types              = VehicleMaintenanceType::orderBy('name', 'ASC')->pluck('name', 'id');

            return view('vehicle-maintenance.create', compact('vehicles', 'types'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Vehicle Maintenance') || \Auth::user()->vehicleOfficer) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'vehicle_id' => 'required',
                    'maintenance_type_id' => 'required',
                    'start_date' => 'required|date',
                    'end_date' => 'nullable|date|after_or_equal:start_date',
                    'name' => 'required',
                    'location' => 'required',
                    'file' => 'nullable|mimes:jpeg,png,jpg,pdf,doc,docx,xls,xlsx|max:10480'
                ]
            );
    
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
    
                return redirect()->back()->with('error', $messages->first());
            }
    
            $vehicle = Vehicle::find($request->vehicle_id);

            $document_path = null;
            if ($request->file('file')) {
                $docs = $request->file('file');
                $docName = time() . "_" . date('Y-m-d') . "_" . preg_replace('/\s+/', '', $vehicle->name) . "." . $docs->getClientOriginalExtension();
                $path = $docs->storeAs('uploads/vehicle-maintenances', $docName, 'public');
                $document_path = env('APP_URL') . '/storage/' . $path;
            }
                
            // Create New Vehicle Maintenance
            $maintenance                        = new VehicleMaintenance();
            $maintenance->vehicle_id            = $request->vehicle_id;
            $maintenance->maintenance_type_id   = $request->maintenance_type_id;
            $maintenance->start_date            = $request->start_date;
            $maintenance->end_date              = $request->end_date;
            $maintenance->name                  = $request->name;
            $maintenance->location              = $request->location;
            $maintenance->cost                  = $request->cost;
            $maintenance->file                  = $document_path;
            $maintenance->description           = $request->description;
            $maintenance->save();
    
            if (($request->end_date == null && Carbon::today()->equalTo(Carbon::parse($request->start_date))) || // Check if start date same as today when user doesn't input endate
                (Carbon::today()->between(Carbon::parse($request->start_date), Carbon::parse($request->end_date)))){ // Check today is between two date
                $vehicle->is_active = false;
                $vehicle->save();
            }
    
            return redirect()->back()->with('success', __('Vehicle Maintenance Successfully Created'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleMaintenance $vehicleMaintenance)
    {
        if (\Auth::user()->can('Manage Vehicle Maintenance')) {
            return view('vehicle-maintenance.show', compact('vehicleMaintenance'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VehicleMaintenance $vehicleMaintenance)
    {
        if (\Auth::user()->vehicleOfficer) {
            if (\Auth::user()->vehicleOfficer->is_resricted) {
                $branch_ids = \Auth::user()->vehicleOfficer->accesses?->pluck('branch_id') ?? [];
                $vehicles   = Vehicle::whereIn('branch_id', $branch_ids)->get();
            } else {
                $vehicles   = Vehicle::get();
            }

            foreach ($vehicles as $vehicle) {
                $branch         = $vehicle?->branch?->name ?? '-';
                $vehicle->name  = "{$vehicle->name} | {$vehicle->type} | {$vehicle->police_no} | {$branch}";
            }
            $vehicles           = $vehicles->pluck('name', 'id');
            $types              = VehicleMaintenanceType::orderBy('name', 'ASC')->pluck('name', 'id');

            return view('vehicle-maintenance.edit', compact('vehicles', 'types', 'vehicleMaintenance'));
        } else if (\Auth::user()->type != 'employee' && \Auth::user()->can('Edit Vehicle Maintenance')) {
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

            $vehicles       = $branch_id?->isNotEmpty() ? Vehicle::whereIn('branch_id', $branch_id)->get() : Vehicle::where('is_active', true)->get();
            foreach ($vehicles as $vehicle) {
                $branch         = $vehicle?->branch?->name ?? '-';
                $vehicle->name  = "{$vehicle->name} | {$vehicle->type} | {$vehicle->police_no} | {$branch}";
            }
            $vehicles           = $vehicles->pluck('name', 'id');
            $types              = VehicleMaintenanceType::orderBy('name', 'ASC')->pluck('name', 'id');

            return view('vehicle-maintenance.edit', compact('vehicles', 'types', 'vehicleMaintenance'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VehicleMaintenance $vehicleMaintenance)
    {
        if (\Auth::user()->can('Edit Vehicle Maintenance') || \Auth::user()->vehicleOfficer) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'vehicle_id' => 'required',
                    'maintenance_type_id' => 'required',
                    'start_date' => 'required|date',
                    'end_date' => 'nullable|date|after_or_equal:start_date',
                    'name' => 'required',
                    'location' => 'required',
                    'file' => 'nullable|mimes:jpeg,png,jpg,pdf,doc,docx,xls,xlsx|max:10480'
                ]
            );
    
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
    
                return redirect()->back()->with('error', $messages->first());
            }
    
            $vehicle = Vehicle::find($request->vehicle_id);
    
            $document_path = null;
            if ($request->file('file')) {
                $docs = $request->file('file');
                $docName = time() . "_" . date('Y-m-d') . "_" . preg_replace('/\s+/', '', $vehicle->name) . "." . $docs->getClientOriginalExtension();
                $path = $docs->storeAs('uploads/vehicle-maintenances', $docName, 'public');
                $document_path = env('APP_URL') . '/storage/' . $path;

                // Delete Old file
                $old_filepath = str_replace(env('APP_URL') . '/storage', '../storage/app/public', $vehicleMaintenance->file);
                if (File::exists($old_filepath)) {
                    File::delete($old_filepath);
                }
            }
                
            // Create New Vehicle Maintenance
            $vehicleMaintenance->maintenance_type_id   = $request->maintenance_type_id;
            $vehicleMaintenance->start_date            = $request->start_date;
            $vehicleMaintenance->end_date              = $request->end_date;
            $vehicleMaintenance->name                  = $request->name;
            $vehicleMaintenance->location              = $request->location;
            $vehicleMaintenance->cost                  = $request->cost;
            $vehicleMaintenance->file                  = $document_path;
            $vehicleMaintenance->description           = $request->description;
            $vehicleMaintenance->save();
    
            if (($request->end_date == null && Carbon::today()->equalTo(Carbon::parse($request->start_date))) || // Check if start date same as today when user doesn't input endate
                (Carbon::today()->between(Carbon::parse($request->start_date), Carbon::parse($request->end_date)))){ // Check today is between two date
                $vehicle->is_active = false;
                $vehicle->save();
            }
    
            return redirect()->back()->with('success', __('Vehicle Maintenance Successfully Created'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleMaintenance $vehicleMaintenance)
    {
        if (\Auth::user()->can('Create Vehicle Maintenance') || \Auth::user()->vehicleOfficer) {
            $vehicleMaintenance->delete();

            // Delete Old file
            $filepath = str_replace(env('APP_URL') . '/storage', '../storage/app/public', $vehicleMaintenance->file);
            if (File::exists($filepath)) {
                File::delete($filepath);
            }

            return redirect()->back()->with('success', __('Vehicle Maintenance Successfully Deleted'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
