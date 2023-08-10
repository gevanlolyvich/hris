<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use App\Models\Permit;
use App\Models\PermitType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PermitTypeController extends Controller
{
    public function index()
    {
        if (Auth::user()->type !== 'employee') {
            $permittypes = PermitType::all();

            return view('permittype.index', compact('permittypes'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {

        if (Auth::user()->type !== 'employee') {
            return view('permittype.create');
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->type !== 'employee') {

            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $leavetype             = new PermitType();
            $leavetype->name       = $request->name;
            $leavetype->save();

            return redirect()->route('permittype.index')->with('success', __('Permit Type Successfully Created'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(PermitType $permtitype)
    {
        return redirect()->route('permtitype.index');
    }

    public function edit(PermitType $permittype)
    {
        if (Auth::user()->type !== 'employee') {
            return view('permittype.edit', compact('permittype'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, PermitType $permittype)
    {
        if (Auth::user()->type !== 'employee') {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $permittype->name = $request->name;
            $permittype->save();

            return redirect()->route('permittype.index')->with('success', __('Permit Type Successfully Updated'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(PermitType $permittype)
    {
        if (Auth::user()->type !== 'employee') {
            $leave     = Permit::where('permit_type_id', $permittype->id)->get();
            if (count($leave) == 0) {
                $permittype->delete();
            } else {
                return redirect()->route('permittype.index')->with('error', __('This  permittype has leave. Please remove the leave from this  permittype'));
            }
            return redirect()->route('permittype.index')->with('success', __('Permit Type Successfully Deleted'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
