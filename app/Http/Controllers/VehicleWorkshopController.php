<?php

namespace App\Http\Controllers;

use App\Models\VehicleWorkshop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VehicleWorkshopController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (\Auth::user()->can('Manage Vehicle Workshop')) {
            $workshops = VehicleWorkshop::orderBy('name', 'ASC')->get();

            return view('vehicle-workshop.index', compact('workshops'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (\Auth::user()->can('Create Vehicle Workshop')) {
            return view('vehicle-workshop.create');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Vehicle Workshop')) {
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

            $type           = new VehicleWorkshop();
            $type->name     = $request->name;
            $type->address  = $request->address;
            $type->save();

            return redirect()->route('vehicle-workshop.index')->with('success', __('Vehicle Workshop Successfully Created'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleWorkshop $vehicleWorkshop)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VehicleWorkshop $vehicleWorkshop)
    {
        if (\Auth::user()->can('Edit Vehicle Workshop')) {
            return view('vehicle-workshop.edit', compact('vehicleWorkshop'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VehicleWorkshop $vehicleWorkshop)
    {
        if (\Auth::user()->can('Edit Vehicle Workshop')) {
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

            $vehicleWorkshop->name      = $request->name;
            $vehicleWorkshop->address   = $request->address;
            $vehicleWorkshop->save();

            return redirect()->route('vehicle-workshop.index')->with('success', __('Vehicle Workshop Successfully Updated'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleWorkshop $vehicleWorkshop)
    {
        if (\Auth::user()->can('Delete Vehicle Workshop')) {
            $vehicleWorkshop->delete();

            return redirect()->route('vehicle-workshop.index')->with('success', __('Vehicle Maintenance Type Successfully Deleted'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
