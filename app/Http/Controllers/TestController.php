<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEmployee;
use App\Models\AttendanceStatus;
use App\Models\Employee;
use App\Models\LogSyncAttendance;
use App\Models\ShiftTime;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class TestController extends Controller
{
    function get_attendances()
    {
        $units = ['Head Office'];
        $apis =['http://172.16.0.11:3050'];
        $locations = ['-6.233798952272397, 106.8479844300084'];
        $date = date('Y-m-d');
        $employees = Employee::where('is_active', 1)->get();

        for ($a=0; $a < count($apis); $a++) { 
            $responses = Http::withHeaders([
                'X-APP-KEY' => 'PTJAKTOURJXBPTJAKTOURJXBPTJAKTOURJXBACCESSDOOOR'
            ])->get($apis[$a] . '/transaction-attendances?date=' . $date);
            $attendances = $responses['attendances'];
    
            $match_data = [];
            for ($i = 0; $i < count($attendances); $i++) {
                for ($j = 0; $j < count($employees); $j++) {
    
                    //* Matching NRK in HRIS and Attendance in Access Door
                    if (($employees[$j]->employee_id == $attendances[$i]['nrk'] || $employees[$j]->personel_id == $attendances[$i]['nrk']) && $attendances[$i]['first_time'] !=  null) { 
                        array_push($match_data, $attendances[$i]);
    
                        //* Method Create Attendance
                        $startTime  = Utility::getValByName('company_start_time');
                        $endTime    = Utility::getValByName('company_end_time');
                        $employee = Employee::where('employee_id', '=', $employees[$j]->employee_id)->first();
                        // return $employee;
                        $attendance = AttendanceEmployee::where('employee_id', '=', $employees[$j]->id)->where('date', '=', $date)->first();
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
                            $employeeAttendance->employee_id   = $employees[$j]->id;
                            $employeeAttendance->date          = $date;
                            $employeeAttendance->status        = 'Present';
                            $employeeAttendance->clock_in      = $attendances[$i]['first_time'] . ':00';
                            $employeeAttendance->clock_out     = $attendances[$i]['last_time'] . ':00';
                            $employeeAttendance->late          = $late;
                            $employeeAttendance->early_leaving = $earlyLeaving;
                            $employeeAttendance->overtime      = $overtime;
                            $employeeAttendance->total_rest    = '00:00:00';
                            $employeeAttendance->created_by    = $employee->user_id;
                            $employeeAttendance->attendance_type_id    = 1; //* ON SITE
                            $employeeAttendance->coord_in      = $locations[$a];
                            $employeeAttendance->coord_out     = $locations[$a];
                            $employeeAttendance->is_valid      = true;
                            $employeeAttendance->validate_by   = 1; //* System
                            $employeeAttendance->save();
                        } else { 
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
            
                            $employeeAttendance                = $attendance;
                            $employeeAttendance->employee_id   = $employees[$j]->id;
                            $employeeAttendance->date          = $date;
                            $employeeAttendance->status        = 'Present';
                            $employeeAttendance->clock_in      = $attendances[$i]['first_time'] . ':00';
                            $employeeAttendance->clock_out     = $attendances[$i]['last_time'] . ':00';
                            $employeeAttendance->late          = $late;
                            $employeeAttendance->early_leaving = $earlyLeaving;
                            $employeeAttendance->overtime      = $overtime;
                            $employeeAttendance->total_rest    = '00:00:00';
                            $employeeAttendance->created_by    = $employee->user_id;
                            $employeeAttendance->attendance_type_id    = 1; //* ON SITE
                            $employeeAttendance->coord_in      = $locations[$a];
                            $employeeAttendance->coord_out     = $locations[$a];
                            $employeeAttendance->is_valid      = true;
                            $employeeAttendance->validate_by   = 1; //* System
                            $employeeAttendance->save();
                        }
                    }
                }
            }
            
            LogSyncAttendance::insert(['status'=>'Success','date'=>$date,'unit'=>$units[$a],'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
        }
        return $match_data;
        return $attendances;
        return $employees;
    }

    function new_get_attendances()
    {
        $units = ['Head Office'];
        $apis =['http://172.16.0.11:3050'];
        $locations = ['-6.233798952272397, 106.8479844300084'];
        $date = date('Y-m-d');
        $tomorrow = date("Y-m-d", strtotime('tomorrow'));
        $employees = Employee::where('is_active', 1)->get();
        $presentAttendance = AttendanceStatus::where('id',1)->first();
        // return $tomorrow;

        for ($a=0; $a < count($apis); $a++) { 
            $responses = Http::withHeaders([
                'X-APP-KEY' => 'PTJAKTOURJXBPTJAKTOURJXBPTJAKTOURJXBACCESSDOOOR'
            ])->get($apis[$a] . '/transaction-attendances?date=' . $date);
            $attendances = $responses['attendances'];
    
            $match_data = [];
            for ($i = 0; $i < count($attendances); $i++) {
                for ($j = 0; $j < count($employees); $j++) {
    
                    //* Matching NRK in HRIS and Attendance in Access Door
                    if (($employees[$j]->employee_id == $attendances[$i]['nrk'] || $employees[$j]->personel_id == $attendances[$i]['nrk']) && $attendances[$i]['first_time'] !=  null) { 
                        array_push($match_data, $attendances[$i]);
    
                        //* Method Create Attendance
                        $shift_times = ShiftTime::where('shift_type_id',$employees[$j]->shift_type->id)
                        ->where('days',date('l'))
                        ->first();

                        
                        if($shift_times->is_working){
                            $startTime = $shift_times->start_time;
                            $endTime = $shift_times->end_time;

                            if(strtotime($startTime) > strtotime($endTime)){
                                continue;
                            }
                            $employee = Employee::where('employee_id', '=', $employees[$j]->employee_id)->first();
                            $attendance = AttendanceEmployee::where('employee_id', '=', $employees[$j]->id)->where('date', '=', $date)->first();
                            
                            if (!$attendance) {
                                $date = date("Y-m-d");
                
                                $totalLateSeconds = strtotime($attendances[$i]['first_time']) - strtotime($date . $startTime);
                
                                $hours = floor($totalLateSeconds / 3600);
                                $mins  = floor($totalLateSeconds / 60 % 60);
                                $secs  = floor($totalLateSeconds % 60);
                                $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                
                                //work hours
                                $totalWorkHoursSeconds    = strtotime($attendances[$i]['last_time']) - strtotime($attendances[$i]['first_time']);
                                $hours                    = floor($totalWorkHoursSeconds / 3600);
                                $mins                     = floor($totalWorkHoursSeconds / 60 % 60);
                                $secs                     = floor($totalWorkHoursSeconds % 60);
                                $workHours                = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                                
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
                                $employeeAttendance->employee_id   = $employees[$j]->id;
                                $employeeAttendance->date          = $date;
                                $employeeAttendance->attendance_status_id = $presentAttendance->id;
                                $employeeAttendance->status        = $presentAttendance->name;
                                $employeeAttendance->clock_in      = $attendances[$i]['first_time'] . ':00';
                                $employeeAttendance->clock_out     = $attendances[$i]['last_time'] . ':00';
                                $employeeAttendance->late          = $late;
                                $employeeAttendance->early_leaving = $earlyLeaving;
                                $employeeAttendance->work_hours    = $workHours;
                                $employeeAttendance->overtime      = $overtime;
                                $employeeAttendance->total_rest    = '00:00:00';
                                $employeeAttendance->created_by    = $employee->user_id;
                                $employeeAttendance->attendance_type_id    = 1; //* ON SITE
                                $employeeAttendance->coord_in      = $locations[$a];
                                $employeeAttendance->coord_out     = $locations[$a];
                                $employeeAttendance->is_valid      = true;
                                $employeeAttendance->validate_by   = 1; //* System
                                $employeeAttendance->save();
                            } else { 
                                $date = date("Y-m-d");
                
                                $totalLateSeconds = strtotime($attendances[$i]['first_time']) - strtotime($date . $startTime);
                
                                $hours = floor($totalLateSeconds / 3600);
                                $mins  = floor($totalLateSeconds / 60 % 60);
                                $secs  = floor($totalLateSeconds % 60);
                                $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                                //work hours
                                if(strtotime($attendances[$i]['last_time']) >strtotime($attendances[$i]['first_time'])){
                                    $totalWorkHoursSeconds    = strtotime($attendances[$i]['last_time']) - strtotime($attendances[$i]['first_time']);
                                }else{
                                    $totalWorkHoursSeconds    = strtotime($date.$attendances[$i]['last_time']) - strtotime($tomorrow.$attendances[$i]['first_time']);
                                }
                                $hours                    = floor($totalWorkHoursSeconds / 3600);
                                $mins                     = floor($totalWorkHoursSeconds / 60 % 60);
                                $secs                     = floor($totalWorkHoursSeconds % 60);
                                $workHours                = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                
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
                
                                $employeeAttendance                = $attendance;
                                $employeeAttendance->employee_id   = $employees[$j]->id;
                                $employeeAttendance->date          = $date;
                                $employeeAttendance->attendance_status_id = $presentAttendance->id;
                                $employeeAttendance->status        = $presentAttendance->name;
                                $employeeAttendance->clock_in      = $attendances[$i]['first_time'] . ':00';
                                $employeeAttendance->clock_out     = $attendances[$i]['last_time'] . ':00';
                                $employeeAttendance->late          = $late;
                                $employeeAttendance->early_leaving = $earlyLeaving;
                                $employeeAttendance->work_hours    = $workHours;
                                $employeeAttendance->overtime      = $overtime;
                                $employeeAttendance->total_rest    = '00:00:00';
                                $employeeAttendance->created_by    = $employee->user_id;
                                $employeeAttendance->attendance_type_id    = 1; //* ON SITE
                                $employeeAttendance->coord_in      = $locations[$a];
                                $employeeAttendance->coord_out     = $locations[$a];
                                $employeeAttendance->is_valid      = true;
                                $employeeAttendance->validate_by   = 1; //* System
                                $employeeAttendance->save();
                            }
                        }else{
                            $startTime  = Utility::getValByName('company_start_time');
                            $endTime    = Utility::getValByName('company_end_time');
                            $employee = Employee::where('employee_id', '=', $employees[$j]->employee_id)->first();
                            $attendance = AttendanceEmployee::where('employee_id', '=', $employees[$j]->id)->where('date', '=', $date)->first();
                            
                            if (!$attendance) {
                                $date = date("Y-m-d");
                                
                                $employeeAttendance                = new AttendanceEmployee();
                                $employeeAttendance->employee_id   = $employees[$j]->id;
                                $employeeAttendance->date          = $date;
                                $employeeAttendance->attendance_status_id = $presentAttendance->id;
                                $employeeAttendance->status        = $presentAttendance->name;
                                $employeeAttendance->clock_in      = $attendances[$i]['first_time'] . ':00';
                                $employeeAttendance->clock_out     = $attendances[$i]['last_time'] . ':00';
                                $employeeAttendance->late          = '00:00:00';
                                $employeeAttendance->early_leaving = '00:00:00';
                                $employeeAttendance->work_hours    = '00:00:00';
                                $employeeAttendance->overtime      = '00:00:00';
                                $employeeAttendance->total_rest    = '00:00:00';
                                $employeeAttendance->created_by    = $employee->user_id;
                                $employeeAttendance->attendance_type_id    = 1; //* ON SITE
                                $employeeAttendance->coord_in      = $locations[$a];
                                $employeeAttendance->coord_out     = $locations[$a];
                                $employeeAttendance->is_valid      = true;
                                $employeeAttendance->validate_by   = 1; //* System
                                $employeeAttendance->save();
                            } else { 
                                $date = date("Y-m-d");
                           
                                $employeeAttendance                = $attendance;
                                $employeeAttendance->employee_id   = $employees[$j]->id;
                                $employeeAttendance->date          = $date;
                                $employeeAttendance->attendance_status_id = $presentAttendance->id;
                                $employeeAttendance->status        = $presentAttendance->name;
                                $employeeAttendance->clock_in      = $attendances[$i]['first_time'] . ':00';
                                $employeeAttendance->clock_out     = $attendances[$i]['last_time'] . ':00';
                                $employeeAttendance->late          = '00:00:00';
                                $employeeAttendance->early_leaving = '00:00:00';
                                $employeeAttendance->work_hours    = '00:00:00';
                                $employeeAttendance->overtime      = '00:00:00';
                                $employeeAttendance->total_rest    = '00:00:00';
                                $employeeAttendance->created_by    = $employee->user_id;
                                $employeeAttendance->attendance_type_id    = 1; //* ON SITE
                                $employeeAttendance->coord_in      = $locations[$a];
                                $employeeAttendance->coord_out     = $locations[$a];
                                $employeeAttendance->is_valid      = true;
                                $employeeAttendance->validate_by   = 1; //* System
                                $employeeAttendance->save();
                            }
                        }

                        
                    }
                }
            }
            
            LogSyncAttendance::insert(['status'=>'Success','date'=>$date,'unit'=>$units[$a],'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
        }
        return $match_data;
        return $attendances;
        return $employees;
    }
}

