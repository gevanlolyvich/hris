<?php

namespace App\Http\Controllers;

use App\Models\VehicleMaintenanceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VehicleMaintenanceTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (\Auth::user()->can('Manage Vehicle Maintenance Type')) {
            $types = VehicleMaintenanceType::orderBy('name', 'ASC')->get();

            return view('vehicle-maintenance-type.index', compact('types'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (\Auth::user()->can('Create Vehicle Maintenance Type')) {
            return view('vehicle-maintenance-type.create');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Vehicle Maintenance Type')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $type             = new VehicleMaintenanceType();
            $type->name       = $request->name;
            $type->save();

            return redirect()->route('vehicle-maintenance-type.index')->with('success', __('Vehicle Maintenance Type Successfully Created'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleMaintenanceType $vehicleMaintenanceType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VehicleMaintenanceType $vehicleMaintenanceType)
    {
        if (\Auth::user()->can('Edit Vehicle Maintenance Type')) {
            return view('vehicle-maintenance-type.edit', compact('vehicleMaintenanceType'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VehicleMaintenanceType $vehicleMaintenanceType)
    {
        if (\Auth::user()->can('Edit Vehicle Maintenance Type')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $vehicleMaintenanceType->name       = $request->name;
            $vehicleMaintenanceType->save();

            return redirect()->route('vehicle-maintenance-type.index')->with('success', __('Vehicle Maintenance Type Successfully Updated'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleMaintenanceType $vehicleMaintenanceType)
    {
        if (\Auth::user()->can('Delete Vehicle Maintenance Type')) {
            $vehicleMaintenanceType->delete();
            
            return redirect()->route('vehicle-maintenance-type.index')->with('success', __('Vehicle Maintenance Type Successfully Deleted'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
