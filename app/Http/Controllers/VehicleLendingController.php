<?php

namespace App\Http\Controllers;

use App\Exports\VehicleLendingExport;
use App\Models\Branch;
use App\Models\Vehicle;
use App\Models\VehicleLending;
use App\Models\VehicleOfficer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use File;
use App\Notifications\VehicleRequest;
use Maatwebsite\Excel\Facades\Excel;

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
                $vehicles   = Vehicle::where('status', 'active')->whereIn('branch_id', $branch_ids)->get();
            } else {
                $vehicles   = Vehicle::where('status', 'active')->get();
            }

            foreach ($vehicles as $vehicle) {
                $branch         = $vehicle?->branch?->name ?? '-';
                $vehicle->name  = "{$vehicle->name} | {$vehicle->type->name} | {$vehicle->police_no} | {$branch}";
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

            $vehicles       = $branch_id?->isNotEmpty() ? Vehicle::where('status', 'active')->whereIn('branch_id', $branch_id)->get() : Vehicle::where('status', 'active')->get();
            foreach ($vehicles as $vehicle) {
                $branch         = $vehicle?->branch?->name ?? '-';
                $vehicle->name  = "{$vehicle->name} | {$vehicle?->type?->name} | {$vehicle->police_no} | {$branch}";
            }
            $vehicles           = $vehicles->pluck('name', 'id');

            return view('vehicle-lending.create', compact('vehicles'));
        } else {
            $vehicles   = Vehicle::where('status', 'active')->get();

            foreach ($vehicles as $vehicle) {
                $branch         = $vehicle?->branch?->name ?? '-';
                $vehicle->name  = "{$vehicle->name} | {$vehicle?->type?->name} | {$vehicle->police_no} | {$branch}";
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
                'end_date' => 'required|date|after_or_equal:date',
                'purpose' => 'required',
                'sim' => 'nullable|mimes:jpeg,png,jpg,pdf|max:10480'
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }
            
        $vehicle = Vehicle::where('status', 'active')->find($request->vehicle_id);
        if (!$vehicle) {
            return redirect()->back()->with('error', __('Vehicle Unavailable'));
        }

        // Optimistic Condition Based On Vehicle Version
        $currentVersion = $vehicle->version;

        $vehicle->incrementVersion();
                
        // Start A Transaction
        DB::beginTransaction();

        $document_path = null;
        if ($request->file('sim')) {
            $docs = $request->file('sim');
            $docName = time() . "_" . date('Y-m-d') . "_" . preg_replace('/\s+/', '', \Auth::user()->name) . "." . $docs->getClientOriginalExtension();
            $path = $docs->storeAs('uploads/sim', $docName, 'public');
            $document_path = env('APP_URL') . '/storage/' . $path;
        }

        // Create New Vehicle Officer
        $lending                = new VehicleLending();
        $lending->request_by    = \Auth::user()->id;
        $lending->vehicle_id    = $request->vehicle_id;
        $lending->date          = $request->date;
        $lending->end_date      = $request->end_date;
        $lending->sim           = $document_path;
        $lending->purpose       = $request->purpose;
        $lending->save();


        // Check Vehicle Version
        $vehicle_reload = Vehicle::select('version')->where('status', 'active')->find($vehicle->id);
        if ($vehicle_reload?->version !== $currentVersion + 1) { // When version not the same as we first retrieve rollback
            DB::rollBack();
            return redirect()->back()->with('error', __('Vehicle Unavailable'));
        }
        // Commit when the version match up
        DB::commit();


        // Send Notification To Vehicle Officers
        // 1. Collect the reciever (subs) data that we need to send
        $officers = VehicleOfficer::where('is_resricted', 0)
            ->orWhereHas('accesses', function ($query) use ($vehicle) {
                $query->where('branch_id', $vehicle->branch_id);
            })
            ->get();
        $subscriptions = [];
        foreach ($officers as $officer) {
            foreach ($officer?->user?->pushNotifications ?? [] as $sub) {
                array_push($subscriptions, ['data' => $sub->data, 'name' => $officer->user->name]);
            }
        }

        // 2. Send push notification to list of reciever (subs)
        $date = substr($request->date,  0, 7);
        \Auth::user()->sendNotifications(
            $subscriptions,
            json_encode([
                'title' => __('New Vehicle Lending Request'),
                'body' => \Auth::user()->name . '  ' . __('Make Vehicle Lending Request') . "{$vehicle->name} [{$vehicle->police_no}] " . __('On Date') . ' ' . $request->date,
                'url' => "/vehicle-lending?type=monthly&month={$date}&date=&branch="
            ]),
            'normal'
        );

        return redirect()->back()->with('success', __('Vehicle Lending Successfully Created'));
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
        $date       = $vehicleLending->date;
        $end_date   = $vehicleLending->end_date;
        $unavailable_vehicle_id = VehicleLending::whereNot('id', $vehicleLending->id)->where(function ($query) use ($date, $end_date) {
            $query->whereBetween('date', [$date, $end_date])
                ->orWhereBetween('end_date', [$date, $end_date])
                ->orWhere(function ($query) use ($date, $end_date) {
                    $query->where('date', '<=', $date)
                            ->where('end_date', '>=', $end_date);
                });
        })->select('vehicle_id')->get()->pluck('vehicle_id');

        if (\Auth::user()->vehicleOfficer) {
            if (\Auth::user()->vehicleOfficer->is_resricted) {
                $branch_ids = \Auth::user()->vehicleOfficer->accesses?->pluck('branch_id') ?? [];
                $vehicles   = Vehicle::where('status', 'active')->whereNotIn('id', $unavailable_vehicle_id)->whereIn('branch_id', $branch_ids)->get();
            } else {
                $vehicles   = Vehicle::where('status', 'active')->whereNotIn('id', $unavailable_vehicle_id)->get();
            }

            foreach ($vehicles as $vehicle) {
                $branch         = $vehicle?->branch?->name ?? '-';
                $vehicle->name  = "{$vehicle->name} | {$vehicle->type->name} | {$vehicle->police_no} | {$branch}";
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
                                Vehicle::where('status', 'active')->whereNotIn('id', $unavailable_vehicle_id)->whereIn('branch_id', $branch_id)->get() :
                                Vehicle::where('status', 'active')->whereNotIn('id', $unavailable_vehicle_id)->get();
            foreach ($vehicles as $vehicle) {
                $branch         = $vehicle?->branch?->name ?? '-';
                $vehicle->name  = "{$vehicle->name} | {$vehicle?->type?->name} | {$vehicle->police_no} | {$branch}";
            }
            $vehicles           = $vehicles->pluck('name', 'id');

            return view('vehicle-lending.edit', compact('vehicles', 'vehicleLending'));
        } else {
            $vehicles   = Vehicle::where('status', 'active')->whereNotIn('id', $unavailable_vehicle_id)->get();

            foreach ($vehicles as $vehicle) {
                $branch         = $vehicle?->branch?->name ?? '-';
                $vehicle->name  = "{$vehicle->name} | {$vehicle?->type?->name} | {$vehicle->police_no} | {$branch}";
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
                'end_date' => 'required|date|after_or_equal:date',
                'purpose' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $vehicle = Vehicle::where('status', 'active')->find($request->vehicle_id);
        if (!$vehicle) {
            return redirect()->back()->with('error', __('Vehicle Unavailable'));
        }

        // Optimistic Condition Based On Vehicle Version
        $currentVersion = $vehicle->version;

        $vehicle->incrementVersion();
                
        // Start A Transaction
        DB::beginTransaction();

        $document_path = null;
        if ($request->file('sim')) {
            $docs = $request->file('sim');
            $docName = time() . "_" . date('Y-m-d') . "_" . preg_replace('/\s+/', '', \Auth::user()->name) . "." . $docs->getClientOriginalExtension();
            $path = $docs->storeAs('uploads/sim', $docName, 'public');
            $document_path = env('APP_URL') . '/storage/' . $path;

            // Delete Old file
            $old_filepath = str_replace(env('APP_URL') . '/storage', '../storage/app/public', $vehicleLending->sim);
            if (File::exists($old_filepath)) {
                File::delete($old_filepath);
            }
        }

        // Create New Vehicle Officer
        $vehicleLending->vehicle_id     = $request->vehicle_id;
        $vehicleLending->date           = $request->date;
        $vehicleLending->end_date       = $request->end_date;
        $vehicleLending->purpose        = $request->purpose;
        $vehicleLending->sim            = $document_path ?: $vehicleLending->sim;
        $vehicleLending->save();

        // Check Vehicle Version
        $vehicle_reload = Vehicle::where('status', 'active')->select('version')->find($vehicle->id);
        if ($vehicle_reload?->version !== $currentVersion + 1) { // When version not the same as we first retrieve rollback
            DB::rollBack();
            return redirect()->back()->with('error', __('Vehicle Unavailable'));
        }
        // Commit when the version match up
        DB::commit();

        return redirect()->back()->with('success', __('Vehicle Lending Successfully Updated'));
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

            // Delete Old file
            $old_filepath = str_replace(env('APP_URL') . '/storage', '../storage/app/public', $vehicleLending->sim);
            if (File::exists($old_filepath)) {
                File::delete($old_filepath);
            }

            return redirect()->back()->with('success', __('Vehicle Lending Successfully Deleted'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function approval(Request $request)
    {
        if (\Auth::user()->vehicleOfficer || \Auth::user()->type != 'employee') {
            $lending                    = VehicleLending::find($request->lending_id);

            // Check Vehicle Availability
            $vehicle = Vehicle::find($lending->vehicle_id);
            if ($vehicle->status != 'active') {
                return redirect()->back()->with('error', __('Vehicle Unavailable'));
            }

            if ($lending) {

                // Check Vehicle Availability
                $unavailable_vehicle_id = VehicleLending::whereNot('id', $request->lending_id)->where('date', $lending->date)->where('status', 'Approved')->whereNull('return_km')->select('vehicle_id')->get()->pluck('vehicle_id')->toArray();

                if (!in_array($lending->vehicle_id, $unavailable_vehicle_id)) {
                    // Update Lending Status Data
                    $lending->status        = $request->status;
                    $lending->approved_by   = \Auth::user()->id;
                    $lending->save();
                } else {
                    return redirect()->back()->with('error', __('Vehicle Unavailable'));
                }
            }

            // Send push notification to requester
            $subscriptions = [];
            if ($lending->requester->pushNotifications) {
                foreach ($lending->requester->pushNotifications ?? [] as $sub) {
                    array_push($subscriptions, ['data' => $sub->data, 'name' => $lending->requester->name]);
                }
            }
            $status = $request->status == 'Approved' ? 'Approved' : 'Rejected';
            \Auth::user()->sendNotifications(
                $subscriptions,
                json_encode([
                    'title' => __('Vehicle Lending Request') . ' ' . __($status),
                    'body' => __('Vehicle Lending Request') . ' ' . $lending->vehicle->name . ' '. __('For Date') . ' ' . $lending->date . ' '. __($status),
                    'url' => '/vehicle-lending'
                ]),
                'normal'
            );

            return redirect()->back()->with('success', __('Vehicle Lending Status Successfully Updated'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getProof($lending_id)
    {
        $vehicleLending = VehicleLending::find($lending_id);
        if ($vehicleLending) {
            $vehicle        = Vehicle::select('km', 'emoney_balance')->find($vehicleLending->vehicle_id);
            return view('vehicle-lending.proof', compact('vehicleLending', 'vehicle'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function proof(Request $request) {
        $vehicleLending = VehicleLending::find($request->lending_id);
        if ($vehicleLending) {
            $name                   = $vehicleLending->requester?->name ?? ' ';
            $emp_name               = preg_replace('/\s+/', '', $name);
            $pickup_document_path_1 = null;
            $return_document_path_1 = null;
            $pickup_document_path_2 = null;
            $return_document_path_2 = null;

            // Preaparing File From Request;
            // Pick Up Files
            if ($request->hasFile('pickup_file_1')) {
                $pickup_docs_1          = $request->pickup_file_1;
                $pickup_docName_1       = time() . "_" . date('Y-m-d') . "_" . $emp_name . '_pickup_1'  . "." . $pickup_docs_1->getClientOriginalExtension();
                $pickup_path_1          = $pickup_docs_1->storeAs("uploads/vehicle_lendings/{$request->lending_id}/" . $emp_name, $pickup_docName_1, 'public');
                $pickup_document_path_1 = env('APP_URL') . '/storage/' . $pickup_path_1;

                // Delete Old file
                $old_pickup_file_path_1 = str_replace(env('APP_URL') . '/storage', '../storage/app/public', $vehicleLending->pickup_file_1);
                if (File::exists($old_pickup_file_path_1)) {
                    File::delete($old_pickup_file_path_1);
                }
            }
            if ($request->hasFile('pickup_file_2')) {
                $pickup_docs_2          = $request->pickup_file_2;
                $pickup_docName_2       = time() . "_" . date('Y-m-d') . "_" . $emp_name . '_pickup_2'  . "." . $pickup_docs_2->getClientOriginalExtension();
                $pickup_path_2          = $pickup_docs_2->storeAs("uploads/vehicle_lendings/{$request->lending_id}/" . $emp_name, $pickup_docName_2, 'public');
                $pickup_document_path_2 = env('APP_URL') . '/storage/' . $pickup_path_2;

                // Delete Old file
                $old_pickup_file_path_2 = str_replace(env('APP_URL') . '/storage', '../storage/app/public', $vehicleLending->pickup_file_2);
                if (File::exists($old_pickup_file_path_2)) {
                    File::delete($old_pickup_file_path_2);
                }
            }

            // Return Files
            if ($request->hasFile('return_file_1')) {
                $return_docs_1            = $request->return_file_1;
                $return_docName_1         = time() . "_" . date('Y-m-d') . "_" . $emp_name . '_return_1'  . "." . $return_docs_1->getClientOriginalExtension();
                $return_path_1            = $return_docs_1->storeAs("uploads/vehicle_lendings/{$request->lending_id}/" . $emp_name, $return_docName_1, 'public');
                $return_document_path_1   = env('APP_URL') . '/storage/' . $return_path_1;

                // Delete Old file
                $old_return_file_path_1 = str_replace(env('APP_URL') . '/storage', '../storage/app/public', $vehicleLending->return_file_1);
                if (File::exists($old_return_file_path_1)) {
                    File::delete($old_return_file_path_1);
                }
            }
            if ($request->hasFile('return_file_2')) {
                $return_docs_2            = $request->return_file_2;
                $return_docName_2         = time() . "_" . date('Y-m-d') . "_" . $emp_name . '_return_2'  . "." . $return_docs_2->getClientOriginalExtension();
                $return_path_2            = $return_docs_2->storeAs("uploads/vehicle_lendings/{$request->lending_id}/" . $emp_name, $return_docName_2, 'public');
                $return_document_path_2   = env('APP_URL') . '/storage/' . $return_path_2;

                // Delete Old file
                $old_return_file_path_2 = str_replace(env('APP_URL') . '/storage', '../storage/app/public', $vehicleLending->return_file_2);
                if (File::exists($old_return_file_path_2)) {
                    File::delete($old_return_file_path_2);
                }
            }

            // Update Lending Data
            $vehicleLending->pickup_km              = $request->pickup_km ? $request->pickup_km : $vehicleLending->pickup_km;
            $vehicleLending->pickup_emoney_balance  = $request->pickup_emoney_balance ? $request->pickup_emoney_balance : $vehicleLending->pickup_emoney_balance;
            $vehicleLending->pickup_time            = $request->pickup_time ? $request->pickup_time : $vehicleLending->pickup_time;
            $vehicleLending->pickup_file_1          = $pickup_document_path_1 ? $pickup_document_path_1 : $vehicleLending->pickup_file_1;
            $vehicleLending->pickup_file_2          = $pickup_document_path_2 ? $pickup_document_path_2 : $vehicleLending->pickup_file_2;
            $vehicleLending->return_km              = $request->return_km ? $request->return_km : $vehicleLending->return_km;
            $vehicleLending->return_emoney_balance  = $request->return_emoney_balance ? $request->return_emoney_balance : $vehicleLending->return_emoney_balance;
            $vehicleLending->return_time            = $request->return_time ? $request->return_time : $vehicleLending->return_time;
            $vehicleLending->return_file_1          = $return_document_path_1 ? $return_document_path_1 : $vehicleLending->return_file_1;
            $vehicleLending->return_file_2          = $return_document_path_2 ? $return_document_path_2 : $vehicleLending->return_file_2;
            $vehicleLending->save();

            // Update Vehicle Data
            $vehicle                    = Vehicle::find($vehicleLending->vehicle_id);
            $vehicle->km                = $request->return_km ? $request->return_km : $vehicle->km;
            $vehicle->emoney_balance    = $request->return_emoney_balance && $request->return_km  ? $request->return_emoney_balance : $vehicle->emoney_balance;
            $vehicle->save();

            return redirect()->back()->with('success', __('Vehicle Lending Proof Successfully Sent'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getVehicleAvailabilityByDate(Request $request) {
        // Getting blacklisted vehicle id
        $date       = $request->date;
        $end_date   = $request->end_date;
        $lendings   = VehicleLending::where(function ($query) use ($date, $end_date) {
            $query->whereBetween('date', [$date, $end_date])
                ->orWhereBetween('end_date', [$date, $end_date])
                ->orWhere(function ($query) use ($date, $end_date) {
                    $query->where('date', '<=', $date)
                            ->where('end_date', '>=', $end_date);
                });
        })->whereNot('vehicle_id', $request->choosen_vehicle)->whereNull('return_km')->whereNot('status', 'Reject')->select('vehicle_id')->get()->pluck('vehicle_id');

        $vehicles           = Vehicle::where('status', 'active')->whereNotIn('id', $lendings)->get();
        foreach ($vehicles as $vehicle) {
            $branch         = $vehicle?->branch?->name ?? '-';
            $vehicle->name  = "{$vehicle->name} | {$vehicle?->type?->name} | {$vehicle->police_no} | {$branch}";
        }
        $vehicles       = $vehicles->pluck('name', 'id');

        return $vehicles;
    }

    public function exportLendings(Request $request)
    {
        $urlQuery = parse_url($request->url, PHP_URL_QUERY);
        $queryArray = [];
        if (!empty($urlQuery)) {
            foreach (explode('&', $urlQuery) as $query) {
                list($key, $value) = explode('=', $query);
                $queryArray[$key] = $value;
            }

            if ($queryArray['type'] == 'daily') {
                $queryArray['timeFrame'] = $queryArray['date'];
            } elseif ($queryArray['type'] == 'monthly') {
                $queryArray['timeFrame'] = $queryArray['month'];
            }
        } else {
            $queryArray['timeFrame'] = date('Y-m');
        }

        $name = preg_replace('/\s+/', '_', __('Vehicle Lending')) . '_' . $queryArray['timeFrame'];
        $data = Excel::download(new VehicleLendingExport(json_encode($queryArray)), $name . '.xlsx');

        return $data;
    }
}
