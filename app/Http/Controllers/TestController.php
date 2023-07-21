<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEmployee;
use App\Models\Employee;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class TestController extends Controller
{
    function get_attendances()
    {
        $nodejs_url = "http://172.16.0.176:3020";
        $date = date('Y-m-d');
        $responses = Http::withHeaders([
            'X-APP-KEY' => 'PTJAKTOURJXBPTJAKTOURJXBPTJAKTOURJXBACCESSDOOOR'
        ])->get($nodejs_url . '/transaction-attendances?date=' . $date);
        $attendances = $responses['attendances'];

        $employees = Employee::where('is_active', 1)->get();
        $match_data = [];
        for ($i = 0; $i < count($attendances); $i++) {
            for ($j = 0; $j < count($employees); $j++) {

                //* Matching NRK in HRIS and Attendance in Access Door
                if ($employees[$j]->employee_id == $attendances[$i]['nrk'] && $attendances[$i]['first_time'] !=  null) { 
                    array_push($match_data, $attendances[$i]);

                    //* Method Create Attendance
                    $startTime  = Utility::getValByName('company_start_time');
                    $endTime    = Utility::getValByName('company_end_time');
                    $employee = Employee::where('employee_id', '=', $employees[$j]->employee_id)->first();
                    // return $employee;
                    $attendance = AttendanceEmployee::where('employee_id', '=', $employees[$j]->employee_id)->where('date', '=', $date)->first();
                    // return $attendance;
                    if (!$attendance) {
                        $date = date("Y-m-d");
        
                        $totalLateSeconds = strtotime($attendances[$i]['first_time']) - strtotime($date . $startTime);
        
                        $hours = floor($totalLateSeconds / 3600);
                        $mins  = floor($totalLateSeconds / 60 % 60);
                        $secs  = floor($totalLateSeconds % 60);
                        $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
        
                        //early Leaving
                        $totalEarlyLeavingSeconds = strtotime($date . $endTime) - strtotime($attendances[$i]['last_time']);
                        $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                        $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                        $secs                     = floor($totalEarlyLeavingSeconds % 60);
                        $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
        
        
                        if (strtotime($attendances[$i]['last_time']) > strtotime($date . $endTime)) {
                            //Overtime
                            $totalOvertimeSeconds = strtotime($attendances[$i]['last_time']) - strtotime($date . $endTime);
                            $hours                = floor($totalOvertimeSeconds / 3600);
                            $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                            $secs                 = floor($totalOvertimeSeconds % 60);
                            $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                        } else {
                            $overtime = '00:00:00';
                        }
        
                        $employeeAttendance                = new AttendanceEmployee();
                        $employeeAttendance->employee_id   = $employees[$j]->employee_id;
                        $employeeAttendance->date          = $date;
                        $employeeAttendance->status        = 'Present';
                        $employeeAttendance->clock_in      = $attendances[$i]['first_time'] . ':00';
                        $employeeAttendance->clock_out     = $attendances[$i]['last_time'] . ':00';
                        $employeeAttendance->late          = $late;
                        $employeeAttendance->early_leaving = $earlyLeaving;
                        $employeeAttendance->overtime      = $overtime;
                        $employeeAttendance->total_rest    = '00:00:00';
                        $employeeAttendance->created_by    = $employee->user_id;
                        $employeeAttendance->save();
                    } else { 
                        //? Buat method update clock out 
                    }
                }
            }
        }
        return $match_data;
        return $attendances;
        return $employees;
    }
}
