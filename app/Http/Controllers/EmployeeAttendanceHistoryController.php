<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEmployee;
use App\Models\AttendanceStatus;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Support\Facades\Crypt;
use App\Models\ShiftTime;
use App\Models\EmployeeHomeHistory;
use App\Models\User;
use App\Models\Utility;
use App\Models\ShiftHistory;
use App\Models\Overtime;
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
            $branch = !empty(\Auth::user()->branch_id) ? Branch::where('id', \Auth::user()->branch_id)->get()->pluck('name', 'id') : Branch::get()->pluck('name', 'id');
            $branch->prepend('All', '');

            $department = !empty(\Auth::user()->branch_id) ? Department::where('branch_id', \Auth::user()->branch_id)->get()->pluck('name', 'id') : Department::get()->pluck('name', 'id');
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
                    $employees = Employee::where('id', $emp);
                }

                $employees = $employees->orderby('name', 'asc')->get();
            } else {
                $employee = !empty(\Auth::user()->branch_id) ? Employee::where('branch_id', \Auth::user()->branch_id)->orderby('name', 'asc') : Employee::orderby('name', 'asc');
                if (!empty($request->branch)) {
                    $employee->where('branch_id', $request->branch);
                }

                if (!empty($request->department)) {
                    $employee->where('department_id', $request->department);
                }

                $employees = $employee->get();
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
        $overtimes            = Overtime::where('employee_id', $empId)->whereNotNull(['report_document']);

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
            $overtimes->whereBetween(
                'date',
                [
                    $start_date,
                    $end_date,
                ]
            );
        } elseif ($request->type == 'daily' && !empty($request->date)) {
            $attendanceEmployee->where('date', $request->date);
            $overtimes->where('date', $request->date);
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
            $overtimes->whereBetween(
                'date',
                [
                    $start_date,
                    $end_date,
                ]
            );
        }

        $attendanceEmployee  = $attendanceEmployee->orderby('date', 'desc')->get();
        $overtimes           = $overtimes->orderby('date', 'desc')->get();

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

        foreach ($overtimes as $overtime) {
            $overtime_hours = 0;
            if ($overtime->type == 'daily') {
                $overtime_hours = 28800;
            } else {
                if (date('Y-m-d', strtotime($overtime->clock_out)) != date('Y-m-d', strtotime($overtime->clock_in))) {
                    $end = date('Y-m-d', strtotime($overtime->clock_in . ' +1 day'));
                    $overtime_hours = strtotime($end) - strtotime($overtime->clock_in);
                } else {
                    $overtime_hours = strtotime($overtime->clock_out) - strtotime($overtime->clock_in);
                }
            }
            // $overtime_hours  = 0;
            // if ($overtime->type != 'hourly') {
            //     $overtime_hours = 28800; // 8 Hours
            // } else {
            //     $overtime_hours  = strtotime($overtime->clock_out) > strtotime($overtime->clock_in) ? strtotime($overtime->clock_out) - strtotime($overtime->clock_in) : strtotime($overtime->clock_in) - strtotime($overtime->clock_out);
            // }
            $total_overtime += $overtime_hours;

            $hours              = floor($overtime_hours / 3600);
            $mins               = floor($overtime_hours / 60 % 60);
            $secs               = floor($overtime_hours % 60);
            $overtime['total']  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
        }
        $hours                  = floor($total_overtime / 3600);
        $mins                   = floor($total_overtime / 60 % 60);
        $total_overtime         = [ 'hours' => $hours, 'minutes' => $mins];
        $max_overtime           = $employee?->departments?->overtime_limit;
        $overtime_exceed_limit  = $hours >= $max_overtime && !empty($max_overtime);

        // Getting shift changes
        $shift_changes = ShiftHistory::where('employee_id', $empId)->get();
        $home_changes = EmployeeHomeHistory::where('employee_id', $empId)->get();

        return view('employeeattendancehistory.show', compact('employee', 'attendanceEmployee', 'total_late', 'total_early', 'total_workhours', 'total_overtime', 'shift_changes', 'home_changes', 'id', 'overtimes', 'max_overtime', 'overtime_exceed_limit'));
    }
}
