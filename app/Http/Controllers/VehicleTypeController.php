<?php

namespace App\Http\Controllers;

use App\Models\VehicleType;
use Illuminate\Http\Request;

class VehicleTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (\Auth::user()->can('Manage Vehicle Type')) {
            $types = VehicleType::orderBy('name', 'ASC')->get();

            return view('vehicle-type.index', compact('types'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (\Auth::user()->can('Create Vehicle Type')) {
            return view('vehicle-type.create');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Vehicle Type')) {
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

            $type             = new VehicleType();
            $type->name       = $request->name;
            $type->save();

            return redirect()->route('vehicle-type.index')->with('success', __('Vehicle Type Successfully Created'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleType $vehicleType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VehicleType $vehicleType)
    {
        if (\Auth::user()->can('Edit Vehicle Type')) {
            return view('vehicle-type.edit', compact('vehicleType'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VehicleType $vehicleType)
    {
        if (\Auth::user()->can('Edit Vehicle Type')) {
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

            $vehicleType->name       = $request->name;
            $vehicleType->save();

            return redirect()->route('vehicle-type.index')->with('success', __('Vehicle Type Successfully Updated'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleType $vehicleType)
    {
        if (\Auth::user()->can('Delete Vehicle Type')) {
            $vehicleType->delete();
            
            return redirect()->route('vehicle-type.index')->with('success', __('Vehicle Type Successfully Deleted'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
