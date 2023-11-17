<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEmployee;
use App\Models\AttendanceStatus;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Support\Facades\Crypt;
use App\Models\ShiftTime;
use App\Models\User;
use App\Models\Utility;
use App\Models\ShiftHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EmployeeAttendanceHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index(Request $request)
    {
        if (\Auth::user()->can('Manage Attendance')) {
            $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branch->prepend('All', '');

            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('All', '');

            $employees = null;

            if (\Auth::user()->type == 'employee') {

                $emp = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;

                $userId = \Auth::user()->employee->user_id;
                $subordinates = \Auth::user()->employee->subordinatesFlatten();

                // Check if employee managing other employee or not
                if ($subordinates->isNotEmpty()) {
                    $employees = collect();
                    foreach ($subordinates as $subordinate) {
                        $employees->push($subordinate->id);
                    }

                    $employees->push($emp);

                    $employees = Employee::whereIn('id', $employees);
                } else {
                    $employees = Employee::where('employee_id', $emp);
                }

                $employees = $employees->orderby('name', 'asc')->get();
            } else {
                $employee = Employee::where('created_by', \Auth::user()->creatorId());
                if (!empty($request->branch)) {
                    $employee->where('branch_id', $request->branch);
                }

                if (!empty($request->department)) {
                    $employee->where('department_id', $request->department);
                }

                $employees = $employee->orderby('name', 'asc')->get();
            }
            return view('employeeattendancehistory.index', compact('employees', 'branch', 'department'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show($id, Request $request)
    {
        // dd($id);
        $empId        = Crypt::decrypt($id);
        $employee     = Employee::find($empId);
        $employeesId  = $employee->employee_id;

        $attendanceEmployee   = AttendanceEmployee::where('employee_id', $empId);

        if ($request->type == 'monthly' && !empty($request->month)) {
            $month = date('m', strtotime($request->month));
            $year  = date('Y', strtotime($request->month));

            $start_date = date($year . '-' . $month . '-01');
            $end_date   = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

            $attendanceEmployee->whereBetween(
                'date',
                [
                    $start_date,
                    $end_date,
                ]
            );
        } elseif ($request->type == 'daily' && !empty($request->date)) {
            $attendanceEmployee->where('date', $request->date);
        } else {
            $month      = date('m');
            $year       = date('Y');
            $start_date = date($year . '-' . $month . '-01');
            $end_date   = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

            $attendanceEmployee->whereBetween(
                'date',
                [
                    $start_date,
                    $end_date,
                ]
            );
        }

        $attendanceEmployee  = $attendanceEmployee->orderby('date', 'desc')->get();

        // calculating total late
        $total_late = 0;
        foreach ($attendanceEmployee as $attendance) {
            $total_late += strtotime($attendance->late) - strtotime(date('Y-m-d'));
        }

        $hours            = floor($total_late / 3600);
        $mins             = floor($total_late / 60 % 60);
        $total_late       = [ 'hours' => $hours, 'minutes' => $mins];

        // calculating total early
        $total_early = 0;
        foreach ($attendanceEmployee as $attendance) {
            $total_early += strtotime($attendance->early_leaving) - strtotime(date('Y-m-d'));
        }
        $hours            = floor($total_early / 3600);
        $mins             = floor($total_early / 60 % 60);
        $total_early      = [ 'hours' => $hours, 'minutes' => $mins];
        
        // calculating workhours
        $total_workhours = 0;
        foreach ($attendanceEmployee as $attendance) {
            $total_workhours += strtotime($attendance->work_hours) - strtotime(date('Y-m-d'));
        }
        $hours            = floor($total_workhours / 3600);
        $mins             = floor($total_workhours / 60 % 60);
        $total_workhours  = [ 'hours' => $hours, 'minutes' => $mins];

        // calculating overtime
        $total_overtime = 0;
        foreach ($attendanceEmployee as $attendance) {
            $total_overtime += strtotime($attendance->overtime) - strtotime(date('Y-m-d'));
        }
        $hours            = floor($total_overtime / 3600);
        $mins             = floor($total_overtime / 60 % 60);
        $total_overtime   = [ 'hours' => $hours, 'minutes' => $mins];

        // Getting shift changes
        $shift_changes = ShiftHistory::where('employee_id', $empId)->get();

        return view('employeeattendancehistory.show', compact('employee', 'attendanceEmployee', 'total_late', 'total_early', 'total_workhours', 'total_overtime', 'shift_changes', 'id'));
    }
}
