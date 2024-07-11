<?php

namespace App\Http\Controllers;

use App\Models\HealthyTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HealthyReportsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::user()->can('Manage Healthy Report')) {
            $healthy_targets = HealthyTarget::orderBy('activity_name', 'ASC')->get();
            return view('healthy_report.index', compact('healthy_targets'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (Auth::user()->can('Manage Healthy Report')) {
            $activities = [
                'Healthy Steps' => __('Healthy Steps')
            ];
            return view('healthy_report.create', compact('activities'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'activity_name' => 'required',
                'target' => 'required'
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $availabilityCheck = HealthyTarget::where('activity_name', $request->activity_name)->first();
        if ($availabilityCheck) {
            return redirect()->back()->with('error', __('Healthy Report Already Exist'));
        }

        $healthy_target                   = new HealthyTarget();
        $healthy_target->activity_name    = $request->activity_name;
        $healthy_target->target           = $request->target;
        $healthy_target->save();

        return redirect()->back()->with('success', __('Healthy Report Successfully Created'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\HealthyTarget  $healthy_target
     * @return \Illuminate\Http\Response
     */
    public function show(HealthyTarget $healthy_target)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HealthyTarget  $healthy_target
     * @return \Illuminate\Http\Response
     */
    public function edit(HealthyTarget $healthy_target)
    {
        if (Auth::user()->can('Edit Healthy Report')) {
            $activities = [
                'Healthy Steps' => __('Healthy Steps')
            ];
            return view('healthy_report.edit', compact('healthy_target', 'activities'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HealthyTarget  $healthy_target
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, HealthyTarget $healthy_target)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'activity_name' => 'required',
                'target' => 'required'
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $availabilityCheck = HealthyTarget::where('activity_name', $request->activity_name)
            ->whereNot('id', $healthy_target->id)->first();
        if ($availabilityCheck) {
            return redirect()->back()->with('error', __('Healthy Report Already Exist'));
        }

        $healthy_target->activity_name    = $request->activity_name;
        $healthy_target->target           = $request->target;
        $healthy_target->save();

        return redirect()->back()->with('success', __('Healthy Report Successfully Updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HealthyTarget  $healthy_target
     * @return \Illuminate\Http\Response
     */
    public function destroy(HealthyTarget $healthy_target)
    {
        if (Auth::user()->can('Delete Healthy Report')) {
            if (Auth::user()->type != 'employee') {
                if (count($healthy_target->healthy_steps) > 0) {
                    return redirect()->back()->with('error', __('Healthy data still exists, target cannot be deleted'));
                }
                $healthy_target->delete();
                return redirect()->back()->with('success', __('Healthy Report Successfully Deleted'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
    }
}
