<?php

namespace App\Http\Controllers;

use App\Models\Competencies;
use App\Models\HealthyTarget;
use App\Models\Performance_Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HealthyTargetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
    //  * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::user()->can('Create Healthy Target')) {
            $healthy_targets = HealthyTarget::orderBy('activity_name', 'ASC')->get();
            return view('healthy_target.index', compact('healthy_targets'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
    //  * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (Auth::user()->can('Create Healthy Target')) {
            $activities = [
                'Healthy Steps' => __('Healthy Steps')
            ];
            return view('healthy_target.create', compact('activities'));
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
            return redirect()->back()->with('error', __('Healthy Target Already Exist'));
        }

        $healthy_target                   = new HealthyTarget();
        $healthy_target->activity_name    = $request->activity_name;
        $healthy_target->target           = $request->target;
        $healthy_target->save();

        return redirect()->back()->with('success', __('Healthy Target Successfully Created'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Performance_Type  $performance_Type
     * @return \Illuminate\Http\Response
     */
    public function show(Performance_Type $performance_Type)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Performance_Type  $performance_Type
    //  * @return \Illuminate\Http\Response
     */
    public function edit(HealthyTarget $healthy_target)
    {
        if (Auth::user()->can('Edit Healthy Target')) {
            $activities = [
                'Healthy Steps' => __('Healthy Steps')
            ];
            return view('healthy_target.edit', compact('healthy_target', 'activities'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Performance_Type  $performance_Type
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
            return redirect()->back()->with('error', __('Healthy Target Already Exist'));
        }

        $healthy_target->activity_name    = $request->activity_name;
        $healthy_target->target           = $request->target;
        $healthy_target->save();

        return redirect()->back()->with('success', __('Healthy Target Successfully Updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Performance_Type  $performance_Type
     * @return \Illuminate\Http\Response
     */
    public function destroy(HealthyTarget $healthy_target)
    {
        if (Auth::user()->can('Delete Healthy Target')) {
            if (Auth::user()->type != 'employee') {
                //! CHECK DEPENDENCIES BEFORE DELETING
                $healthy_target->delete();
                return redirect()->back()->with('success', __('Healthy Target Successfully Deleted'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
    }
}
