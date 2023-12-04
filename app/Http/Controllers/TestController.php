<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEmployee;
use App\Models\AttendanceStatus;
use App\Models\Employee;
use App\Models\LogSyncAttendance;
use App\Models\LogAttendance;
use App\Models\ShiftTime;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    function new_get_attendances()
    {
        $units = ['Head Office'];
        $apis =['http://172.16.0.11:3050'];
        $locations = '-6.233798952272397, 106.8479844300084';
        $default_coordinate = '-6.233798952272397, 106.8479844300084, 50';
        $date = date('Y-m-d');
        $tomorrow = date("Y-m-d", strtotime('tomorrow'));
        $yesterday = date("Y-m-d", strtotime('yesterday'));
        $employees = Employee::where('is_active', 1)->select('personel_id')->get();
        $presentAttendance = AttendanceStatus::where('id',1)->first();

        for ($a=0; $a < count($apis); $a++) { 
            $responses = Http::withHeaders([
                'X-APP-KEY' => 'PTJAKTOURJXBPTJAKTOURJXBPTJAKTOURJXBACCESSDOOOR'
            ])->get($apis[$a] . '/transaction-attendances?date=' . $date);

            if (!empty($responses)) {
                $parsed_responses = $responses->json();
                $attendances = $parsed_responses['attendances'];
    
                $massAssign = array_map(function ($data) use ($parsed_responses, $default_coordinate, $employees, $attendances){
                    $duplicate_data = LogAttendance::where('personel_id', $data['nrk'])
                        ->where('date', $parsed_responses['date'])
                        ->where('min', $data['first_time'])
                        ->where('max', $data['last_time'])
                        ->first();
                    if (!$duplicate_data && $employees->contains('personel_id', $data['nrk']) && ($data['first_time'] || $data['last_time'])) {
                        return [
                            'personel_id' => $data['nrk'],
                            'date' => $parsed_responses['date'],
                            'coordinate' => $attendances['coordinate'] ?? $default_coordinate,
                            'min' => $data['first_time'],
                            'max' => $data['last_time'],
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ];
                    }
                }, $attendances);
    
                $parsed_data = array_values(array_filter($massAssign, function ($data) {
                    return $data !== null;
                }));
                
                LogAttendance::insert($parsed_data);
    
                $final_data = DB::table('log_attendances')
                    ->select('personel_id', DB::raw('MIN(min) as min'), DB::raw('MAX(max) as max'), 'coordinate', 'date')
                    ->where('date', date('Y-m-d'))
                    ->groupBy('personel_id')
                    ->get();

                foreach ($final_data as $f_data) {
                    $employee = Employee::where('personel_id', $f_data->personel_id)->select('id', 'user_id', 'shift_type_id')->with('shift_type:id')->first();
                    $shift_times = ShiftTime::where('shift_type_id',$employee->shift_type->id)
                        ->where('days',date('l'))
                        ->select(['is_working', 'start_time', 'end_time'])
                        ->first();
                    $yesterday_shift_times = ShiftTime::where('shift_type_id',$employee->shift_type->id)
                        ->where('days',date('l', strtotime('yesterday')))
                        ->select(['is_working', 'start_time', 'end_time'])
                        ->first();
    
                    $attendance = AttendanceEmployee::where('employee_id', $employee->id)->where('date', '=', $date)->first();
                    $yesterday_attendance = AttendanceEmployee::where('employee_id', $employee->id)->where('date', '=', $yesterday)->first();
                    $cross_day = $shift_times->start_time > $shift_times->end_time;
                    $yesterday_cross_day = $yesterday_shift_times->start_time > $yesterday_shift_times->end_time;
    
                    $absolute_in = strtotime($shift_times->start_time) - 3600;
                    $absolute_out = strtotime($shift_times->end_time) + 3600;
    
                    $yesterday_absolute_in = strtotime("$yesterday $yesterday_shift_times->start_time") - 3600;
                    $yesterday_absolute_out = strtotime("$yesterday $yesterday_shift_times->end_time") + 3600;
    
                    $clock_in = $attendance['clock_in'] ?? null;
                    $clock_out = null;
    
                    $yesterday_clock_in = $yesterday_attendance['clock_in'] ?? null;
                    $yesterday_clock_out = null;
    
                    if ($cross_day && strtotime($f_data->max) > $absolute_in  && empty($attendance)) {
                        $clock_in = $f_data->max;
                    } elseif (strtotime($f_data->min) > $absolute_in  && empty($attendance)) {
                        $clock_in = $f_data->min;
                    }
    
                    if ($yesterday_cross_day && strtotime($f_data->max) < $yesterday_absolute_out) {
                        $yesterday_clock_out = $f_data->max;
                    } elseif (strtotime($f_data->max) < $absolute_out) {
                        $clock_out = $f_data->max;
                    }
    
                    // * Calculating late
                    $late = '00:00:00';
                    if (strtotime($clock_in) > strtotime($shift_times->start_time)) {
                        $totalLateSeconds = strtotime($clock_in) - strtotime($shift_times->start_time);
                        $late_hours = floor($totalLateSeconds / 3600);
                        $late_mins  = floor($totalLateSeconds / 60 % 60);
                        $late_secs  = floor($totalLateSeconds % 60);
                        $late  = sprintf('%02d:%02d:%02d', $late_hours, $late_mins, $late_secs);
                    }
    
                    // * Calculating Overtime
                    $overtime = '00:00:00';
                    if (strtotime($clock_out) > strtotime($shift_times->end_time)) {
                        $total_overtime_seconds = strtotime($clock_out) - strtotime($shift_times->end_time);
    
                        $overtime_hours = floor($total_overtime_seconds / 3600);
                        $overtime_mins  = floor($total_overtime_seconds / 60 % 60);
                        $overtime_secs  = floor($total_overtime_seconds % 60);
                        $overtime  = sprintf('%02d:%02d:%02d', $overtime_hours, $overtime_mins, $overtime_secs);
                    }
    
                    // * Calculating Workhours
                    $workhours = '00:00:00';
                    if ($yesterday_cross_day && $yesterday_clock_out && strtotime($yesterday_clock_out) <= $yesterday_absolute_out) {
                        $total_workhour_seconds = strtotime($yesterday_clock_out) - strtotime($yesterday_clock_in);
    
                        $workhour_hours = floor($total_workhour_seconds / 3600);
                        $workhour_mins  = floor($total_workhour_seconds / 60 % 60);
                        $workhour_secs  = floor($total_workhour_seconds % 60);
                        $workhours      = sprintf('%02d:%02d:%02d', $workhour_hours, $workhour_mins, $workhour_secs);
                    } elseif ($clock_in && $clock_out && strtotime($clock_out) > strtotime($clock_in)) {
                        $total_workhour_seconds = strtotime($clock_out) - strtotime($clock_in);
    
                        $workhour_hours = floor($total_workhour_seconds / 3600);
                        $workhour_mins  = floor($total_workhour_seconds / 60 % 60);
                        $workhour_secs  = floor($total_workhour_seconds % 60);
                        $workhours      = sprintf('%02d:%02d:%02d', $workhour_hours, $workhour_mins, $workhour_secs);
                    }
    
                    // * Calculating Early Leaving
                    $early_leaving = '00:00:00';
                    if ($yesterday_cross_day && $yesterday_clock_out && strtotime($yesterday_clock_out) <= strtotime($yesterday_shift_times->end_time)) {
                        $total_early_seconds = strtotime($yesterday_shift_times->end_time) - strtotime($yesterday_clock_out);
                        $early_hours         = floor($total_early_seconds / 3600);
                        $early_mins          = floor($total_early_seconds / 60 % 60);
                        $early_secs          = floor($total_early_seconds % 60);
                        $early_leaving       = sprintf('%02d:%02d:%02d', $early_hours, $early_mins, $early_secs);
                    } elseif (strtotime($clock_out) <= strtotime($shift_times->end_time)) {
                        $total_early_seconds = strtotime($shift_times->end_time) - strtotime($clock_out);
                        $early_hours         = floor($total_early_seconds / 3600);
                        $early_mins          = floor($total_early_seconds / 60 % 60);
                        $early_secs          = floor($total_early_seconds % 60);
                        $early_leaving       = sprintf('%02d:%02d:%02d', $early_hours, $early_mins, $early_secs);
                    }

                    // ? Create / Update Attendance
                    if ($yesterday_clock_out && $yesterday_attendance) {
                        Log::info('Updating Yesterday Attendance Data');
                        $yesterday_attendance->clock_out     = $yesterday_clock_out;
                        $yesterday_attendance->work_hours    = $yesterday_shift_times->is_working ? $workhours : '00:00:00';
                        $yesterday_attendance->overtime      = $yesterday_shift_times->is_working ? $workhours : '00:00:00';
                        $yesterday_attendance->early_leaving = $yesterday_shift_times->is_working ? $early_leaving : '00:00:00';
                        $yesterday_attendance->coord_out     = $f_data->coordinate;
                        $yesterday_attendance->save();
                    } elseif ($attendance && $clock_out) {
                        Log::info('Updating Today Attendance Data');
                        $attendance->clock_out     = $clock_out;
                        $attendance->work_hours    = $shift_times->is_working ? $workhours : '00:00:00';
                        $attendance->overtime      = $shift_times->is_working ? $workhours : '00:00:00';
                        $attendance->early_leaving = $shift_times->is_working ? $early_leaving : '00:00:00';
                        $attendance->coord_out     = $f_data->coordinate;
                        $attendance->save();
                    } elseif ($clock_in && empty($attendance)) {
                        Log::info('Creating New Attendance Data');
                        $new_attendance = new AttendanceEmployee();
                        $new_attendance->employee_id          = $employee->id;
                        $new_attendance->date                 = $date;
                        $new_attendance->attendance_status_id = $presentAttendance->id;
                        $new_attendance->status               = $presentAttendance->name;
                        $new_attendance->clock_in             = $clock_in . ':00';
                        $new_attendance->clock_out            = $clock_out . ':00';
                        $new_attendance->late                 = $shift_times->is_working ? $late : '00:00:00';
                        $new_attendance->work_hours           = $shift_times->is_working ? $workhours : '00:00:00';
                        $new_attendance->overtime             = $shift_times->is_working ? $overtime : '00:00:00';
                        $new_attendance->early_leaving        = $shift_times->is_working ? $early_leaving : '00:00:00';
                        $new_attendance->total_rest           = '00:00:00';
                        $new_attendance->created_by           = $employee->user_id;
                        $new_attendance->attendance_type_id   = 1; //* ON SITE
                        $new_attendance->coord_in             = $f_data->coordinate;
                        $new_attendance->is_valid             = true;
                        $new_attendance->validate_by          = 1; //* System
                        $new_attendance->shift_type_id        = $employee->shift_type_id;
                        $new_attendance->save();
                    }
                }
            }

            LogSyncAttendance::insert(['status'=>'Success','date'=>$date,'unit'=>$units[$a],'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
        }
        return null;
    }
}

