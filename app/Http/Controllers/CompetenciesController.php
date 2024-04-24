<?php

namespace App\Http\Controllers;

use App\Models\AppraisalRating;
use App\Models\Competencies;
use App\Models\IndicatorWeight;
use App\Models\Performance_Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CompetenciesController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('Manage Competencies')) {
            $competencies = Competencies::orderBy('name', 'ASC')->get();
            return view('competencies.index', compact('competencies'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        $performance_types  = Performance_Type::get()->pluck('name', 'id');
        $types              = Competencies::$types;

        foreach ($types as $type) {
            $types[$type] = __("{$type}");
        }

        return view('competencies.create', compact('performance_types', 'types'));
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Competencies')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'type' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $competencies                       = new Competencies();
            $competencies->name                 = $request->name;
            $competencies->type                 = $request->type;
            $competencies->performance_type_id  = $request->performance_type_id;
            $competencies->description          = $request->description;
            $competencies->created_by           = \Auth::user()->id;
            $competencies->save();

            return redirect()->route('competencies.index')->with('success', __('Competencies  successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show(Competencies $competencies)
    {
        //
    }


    public function edit($id)
    {
        $competencies       = Competencies::find($id);
        $performance_types  = Performance_Type::get()->pluck('name', 'id');
        $types              = Competencies::$types;

        foreach ($types as $type) {
            $types[$type] = __("{$type}");
        }

        return view('competencies.edit', compact('types', 'performance_types', 'competencies'));
    }


    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('Edit Competencies')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'type' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $competencies                       = Competencies::find($id);
            $competencies->name                 = $request->name;
            $competencies->type                 = $request->type;
            $competencies->performance_type_id  = $request->performance_type_id;
            $competencies->description          = $request->description;
            $competencies->save();

            return redirect()->route('competencies.index')->with('success', __('Competencies  successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy($id)
    {
        if (\Auth::user()->can('Delete Competencies')) {
            $competencies = Competencies::find($id);
            $competencies->delete();

            IndicatorWeight::where('competency_id', $id)->delete();
            AppraisalRating::where('competency_id', $id)->delete();

            return redirect()->route('competencies.index')->with('success', __('Competencies  successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
