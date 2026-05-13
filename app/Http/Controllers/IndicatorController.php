<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Competencies;
use App\Models\Designation;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Indicator;
use App\Models\IndicatorWeight;
use App\Models\LevelDesignation;
use App\Models\Performance_Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IndicatorController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('Manage Indicator')) {
            $indicators = Indicator::get();

            return view('indicator.index', compact('indicators'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        if (\Auth::user()->can('Create Indicator')) {
            $performance_types  = Performance_Type::whereNull('parent_id')->get();
            $levels             = LevelDesignation::get()->pluck('name', 'id');
            return view('indicator.create', compact('performance_types', 'levels'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Indicator')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'level' => 'required',
                    'competencies' => 'required|array'
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $levelDuplicate         = Indicator::where('level_id', $request->level)->first();
            if ($levelDuplicate) {
                return redirect()->back()->with('error', $levelDuplicate->level->name . ' ' . __('Level Already Has Indicator'));
            }

            $indicator                          = new Indicator();
            $indicator->level_id                = $request->level;
            $indicator->created_by              = \Auth::user()->id;
            $indicator->save();
            
            foreach ($request->competencies as $competency => $weight) {
                if ($weight > 0) {
                    $indicatorWeight                = new IndicatorWeight();
                    $indicatorWeight->competency_id = $competency;
                    $indicatorWeight->indicator_id  = $indicator->id;
                    $indicatorWeight->weight        = $weight;
                    $indicatorWeight->save();
                }
            }

            return redirect()->route('indicator.index')->with('success', __('Indicator successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Indicator $indicator)
    {
        $performance_types  = Performance_Type::whereNull('parent_id')->get();
        $weights            = $indicator->weights->pluck('weight', 'competency_id');

        $processed_weights  = array();
        foreach ($weights as $key => $value) {
            $processed_weights["competency_{$key}"] = $value;
        }

        return view('indicator.show', compact('indicator', 'performance_types', 'processed_weights'));
    }


    public function edit(Indicator $indicator)
    {
        if (\Auth::user()->can('Edit Indicator')) {
            $levels             = LevelDesignation::get()->pluck('name', 'id');
            $performance_types  = Performance_Type::whereNull('parent_id')->get();
            $weights            = $indicator->weights->pluck('weight', 'competency_id');

            $processed_weights  = array();
            foreach ($weights as $key => $value) {
                $processed_weights["competency_{$key}"] = $value;
            }

            return view('indicator.edit', compact('performance_types', 'indicator', 'processed_weights', 'levels'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function update(Request $request, Indicator $indicator)
    {
        if (\Auth::user()->can('Edit Indicator')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'level_id' => 'required',
                    'competencies' => 'required|array'
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $levelDuplicate         = Indicator::whereNot('id', $indicator->id)->where('level_id', $request->level_id)->first();
            if ($levelDuplicate) {
                return redirect()->back()->with('error', $levelDuplicate->level->name . ' ' . __('Level Already Has Indicator'));
            }

            $indicator->level_id    = $request->level_id;
            $indicator->save();

            foreach ($request->competencies as $competency => $weight) {
                $indicator_weight                   = IndicatorWeight::where('indicator_id', $indicator->id)->where('competency_id', $competency)->first();
                if ($indicator_weight) {
                    $indicator_weight->weight       = $weight;
                    $indicator_weight->save();
                } else {
                    if ($weight > 0) {
                        $indicatorWeight                = new IndicatorWeight();
                        $indicatorWeight->competency_id = $competency;
                        $indicatorWeight->indicator_id  = $indicator->id;
                        $indicatorWeight->weight        = $weight;
                        $indicatorWeight->save();
                    }
                }
            }

            return redirect()->route('indicator.index')->with('success', __('Indicator successfully updated.'));
        }
    }


    public function destroy(Indicator $indicator)
    {
        if (\Auth::user()->can('Delete Indicator')) {
            $indicator->delete();

            IndicatorWeight::where('indicator_id', $indicator->id)->delete();

            return redirect()->back()->with('success', __('Indicator successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
