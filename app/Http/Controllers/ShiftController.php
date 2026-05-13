<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\ShiftTime;
use App\Models\ShiftType;
use App\Models\Warning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ShiftController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('Manage Shift')) {
            $shifts = !empty(\Auth::user()->branch_id) ? ShiftType::where('branch_id', \Auth::user()->branch_id)->orderBy('name', 'ASC')->get() : ShiftType::orderBy('name', 'ASC')->get();

            return view('shift.index', compact('shifts'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Shift')) {
            $branches = !empty(\Auth::user()->branch_id) ? Branch::where('id', \Auth::user()->branch_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id') : Branch::orderBy('name', 'ASC')->get()->pluck('name', 'id');

            return view('shift.create', compact('branches'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        // return $request;
        if (\Auth::user()->can('Create Shift')) {

            $validator = Validator::make(
                $request->all(),
                [
                    'shift_name' => 'required',
                    "branch_id"  => 'required'
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $status['status1'] = isset($request->status1) ? true : false;
            $status['status2'] = isset($request->status2) ? true : false;
            $status['status3'] = isset($request->status3) ? true : false;
            $status['status4'] = isset($request->status4) ? true : false;
            $status['status5'] = isset($request->status5) ? true : false;
            $status['status6'] = isset($request->status6) ? true : false;
            $status['status7'] = isset($request->status7) ? true : false;

            $shift_type = ShiftType::create([
                'name' => $request->shift_name,
                'branch_id' => $request->branch_id
            ]);
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

            for ($i = 0; $i < count($days); $i++) {
                $index_status = $i + 1;
                $start_time = ($status["status{$index_status}"]) ? $request->company_start_time[$i] . ':00' : null;
                $end_time = ($status["status{$index_status}"]) ? $request->company_end_time[$i] . ':00' : null;

                ShiftTime::create([
                    'shift_type_id' => $shift_type->id,
                    'days'          => $days[$i],
                    'is_working'    => $status["status{$index_status}"],
                    'start_time'    => $start_time,
                    'end_time'      => $end_time,
                ]);
            }

            return redirect()->route('shift.index')->with('success', __('Shift successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit(ShiftType $shift)
    {
        // return $shift;
        if (\Auth::user()->can('Edit Shift')) {
            $shift_type = ShiftType::find($shift->id);
            $branches = Branch::get()->pluck('name', "id");

            return view('shift.edit', compact('shift', 'shift_type', 'branches'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, ShiftType $shift)
    {
        if (\Auth::user()->can('Edit Shift')) {
            if (Auth::user()->type != 'employee') {
                $validator = Validator::make(
                    $request->all(),
                    [
                        'shift_name' => 'required',
                        "branch_id"  => 'required'
                    ]
                );
            }

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            //* Update Shift Type
            $shift->name        = $request->shift_name;
            $shift->branch_id   = $request->branch_id;
            $shift->save();

            //* Update Shift Times

            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $status['status1'] = isset($request->status1) ? true : false;
            $status['status2'] = isset($request->status2) ? true : false;
            $status['status3'] = isset($request->status3) ? true : false;
            $status['status4'] = isset($request->status4) ? true : false;
            $status['status5'] = isset($request->status5) ? true : false;
            $status['status6'] = isset($request->status6) ? true : false;
            $status['status7'] = isset($request->status7) ? true : false;

            for ($i = 0; $i < count($days); $i++) {
                $index_status = $i + 1;
                $start_time = ($status["status{$index_status}"]) ? $request->company_start_time[$i] . ':00' : null;
                $end_time = ($status["status{$index_status}"]) ? $request->company_end_time[$i] . ':00' : null;

                ShiftTime::where('shift_type_id',  $shift->id)
                    ->where('days', $days[$i])
                    ->update([
                        'days'          => $days[$i],
                        'is_working'    => $status["status{$index_status}"],
                        'start_time'    => $start_time,
                        'end_time'      => $end_time,
                    ]);
            }
            return redirect()->route('shift.index')->with('success', __('Shift successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // * Soft Delete
    public function destroy(ShiftType $shift)
    {
        if (\Auth::user()->can('Delete Shift')) {
            //* Destroy shift times 
            $shift_times = ShiftTime::where('shift_type_id', $shift->id)->get()->pluck('id');
            ShiftTime::destroy($shift_times);

            // * Destroy shift type
            $shift->delete();
            return redirect()->route('shift.index')->with('success', __('Shift successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
