<?php

namespace App\Http\Controllers;

use App\Models\Bpjs;
use App\Models\BpjsOption;
use Illuminate\Http\Request;

class BpjsOptionController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('Manage Bpjs Option')) {
            $bpjsoptions = BpjsOption::orderBy('id', 'ASC')->get();

            return view('bpjsoption.index', compact('bpjsoptions'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Bpjs Option')) {
            return view('bpjsoption.create');
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Bpjs Option')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required|max:100',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $bpjsoption             = new BpjsOption();
            $bpjsoption->name       = $request->name;
            $bpjsoption->created_by = \Auth::user()->creatorId();
            $bpjsoption->save();

            return redirect()->route('bpjsoption.index')->with('success', __('BpjsOption  successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(BpjsOption $bpjsoption)
    {
        return redirect()->route('bpjsoption.index');
    }

    public function edit(BpjsOption $bpjsoption)
    {
        if (\Auth::user()->can('Edit Bpjs Option')) {
            if ($bpjsoption->created_by == \Auth::user()->creatorId()) {

                return view('bpjsoption.edit', compact('bpjsoption'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, BpjsOption $bpjsoption)
    {
        if (\Auth::user()->can('Edit Bpjs Option')) {
            if ($bpjsoption->created_by == \Auth::user()->creatorId()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name' => 'required|max:100',

                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }
                $bpjsoption->name = $request->name;
                $bpjsoption->save();

                return redirect()->route('bpjsoption.index')->with('success', __('BpjsOption successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(BpjsOption $bpjsoption)
    {
        if (\Auth::user()->can('Delete Bpjs Option')) {
            if ($bpjsoption->created_by == \Auth::user()->creatorId()) {
                $bpjs     = Bpjs::where('bpjs_option', $bpjsoption->id)->get();
                if (count($bpjs) == 0) {
                    $bpjsoption->delete();
                } else {
                    return redirect()->route('bpjsoption.index')->with('error', __('This Bpjs Option has Bpjs. Please remove the Bpjs from this Bpjs option.'));
                }

                return redirect()->route('bpjsoption.index')->with('success', __('BpjsOption successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
