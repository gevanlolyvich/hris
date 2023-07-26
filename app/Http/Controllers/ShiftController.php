<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Warning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    public function index()
    {
        if(Auth::user()->can('Manage Warning'))
        {
            if(Auth::user()->type == 'employee')
            {
                $emp      = Employee::where('user_id', '=', Auth::user()->id)->first();
                $warnings = Warning::where('warning_by', '=', $emp->id)->get();
            }
            else
            {
                $warnings = Warning::where('created_by', '=', Auth::user()->creatorId())->get();
            }

            return view('shift.index',compact('warnings'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(Auth::user()->can('Create Warning'))
        {
            if(Auth::user()->type == 'employee')
            {
                $user             = Auth::user();
                $current_employee = Employee::where('user_id', $user->id)->get()->pluck('name', 'id');
                $employees        = Employee::where('user_id', '!=', $user->id)->get()->pluck('name', 'id');
            }
            else
            {
                $user             = Auth::user();
                $current_employee = Employee::where('user_id', $user->id)->get()->pluck('name', 'id');
                $employees        = Employee::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
            }

            return view('shift.create', compact('employees', 'current_employee'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }
}
