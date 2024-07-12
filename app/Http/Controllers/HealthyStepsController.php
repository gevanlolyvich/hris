<?php

namespace App\Http\Controllers;

use App\Models\Competencies;
use App\Models\HealthyStep;
use App\Models\HealthyTarget;
use App\Models\Performance_Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HealthyStepsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::user()->can('Create Healthy Steps')) {
            $healthy_steps = !empty(Auth::user()->employee->id) ?
                HealthyStep::orderBy('date', 'DESC')->where('employee_id', Auth::user()->employee->id)->get() :
                HealthyStep::orderBy('date', 'DESC')->get();

            return view('healthy_step.index', compact('healthy_steps'));
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
        if (Auth::user()->can('Create Healthy Steps')) {
            $activities = [
                'Healthy Steps' => __('Healthy Steps')
            ];
            return view('healthy_step.create', compact('activities'));
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
                'date' => 'required|date|before_or_equal:today',
                'steps' => 'required',
                'attachment' => 'required'
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $healthy_steps_target = HealthyTarget::where('activity_name', "Healthy Steps")->first();
        if (empty($healthy_steps_target)) {
            return redirect()->back()->with('error', __('Healthy Steps Target Not Yet Set'));
        }

        $duplicateCheck = HealthyStep::where('date', $request->date)
            ->where('employee_id', Auth::user()->employee->id)->first();
        if ($duplicateCheck) {
            return redirect()->back()->with('error', __('Healthy Steps Already Exist'));
        }

        $document_path = null;
        if ($request->file('attachment')) {
            $docs = $request->file('attachment');
            $docName = time() . "_" . date('Y-m-d') . "_" . preg_replace('/\s+/', '', Auth::user()->name) . "." . $docs->getClientOriginalExtension();
            $path = $docs->storeAs('uploads/healthy_steps', $docName, 'public');
            $document_path = env('APP_URL') . '/storage/' . $path;
        }

        $healthy_step               = new HealthyStep();
        $healthy_step->employee_id  = Auth::user()->employee->id;
        $healthy_step->target_id    = $healthy_steps_target->id;
        $healthy_step->date         = $request->date;
        $healthy_step->steps        = $request->steps;
        $healthy_step->attachment   = $document_path;
        $healthy_step->save();


        return redirect()->back()->with('success', __('Healthy Steps Successfully Created'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\HealthyStep  $performance_Type
     * @return \Illuminate\Http\Response
     */
    public function show(HealthyStep $healthy_step)
    {
        return view('healthy_step.show', compact('healthy_step'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Performance_Type  $performance_Type
    //  * @return \Illuminate\Http\Response
     */
    public function edit(HealthyStep $healthy_step)
    {
        if (Auth::user()->can('Edit Healthy Steps')) {
            return view('healthy_step.edit', compact('healthy_step'));
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
    public function update(Request $request, HealthyStep $healthy_step)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'steps' => 'required'
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $document_path = null;
        if ($request->file('attachment')) {
            $docs = $request->file('attachment');
            $docName = time() . "_" . date('Y-m-d') . "_" . preg_replace('/\s+/', '', Auth::user()->name) . "." . $docs->getClientOriginalExtension();
            $path = $docs->storeAs('uploads/healthy_steps', $docName, 'public');
            $document_path = env('APP_URL') . '/storage/' . $path;
        }

        $healthy_step->steps      = $request->steps;
        $healthy_step->attachment = empty($document_path) ? $healthy_step->attachment : $document_path;
        $healthy_step->save();

        return redirect()->back()->with('success', __('Healthy Steps Successfully Updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Performance_Type  $performance_Type
     * @return \Illuminate\Http\Response
     */
    public function destroy(HealthyStep $healthy_step)
    {
        if (Auth::user()->can('Delete Healthy Steps')) {
            $healthy_step->delete();
            return redirect()->back()->with('success', __('Healthy Steps Successfully Deleted'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
