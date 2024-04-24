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
        $apis =['http://172.16.0.16:3050'];
        $locations = '-6.172612489913187, 106.8627610802651';
        $default_coordinate = '-6.172612489913187, 106.8627610802651, 20';
        $date = date('Y-m-d');
        $tomorrow = date("Y-m-d", strtotime('tomorrow'));
        $yesterday = date("Y-m-d", strtotime('yesterday'));
        $employees = Employee::where('is_active', 1)->select('personel_id')->get();
        $presentAttendance = AttendanceStatus::where('id',1)->first();

        $settings = Utility::settings();

        for ($a=0; $a < count($apis); $a++) {
            $responses = null;
            try {
                $responses = Http::withHeaders([
                    'X-APP-KEY' => 'PTJAKTOURJXBPTJAKTOURJXBPTJAKTOURJXBACCESSDOOOR'
                ])->get($apis[$a] . '/transaction-attendances?date=' . $date);
            } catch (\Throwable $th) {
                $responses = null;
                Log::info($th);
            }

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
                            'personel_id'       => $data['nrk'],
                            'date'              => $parsed_responses['date'],
                            'coordinate'        => $attendances['coordinate'] ?? $default_coordinate,
                            'coordinate_out'    => $attendances['coordinate'] ?? $default_coordinate,
                            'min'               => $data['first_time'],
                            'max'               => $data['last_time'],
                            'min_source'        => $data['first_source'],
                            'max_source'        => $data['last_source'],
                            'created_at'        => date('Y-m-d H:i:s'),
                            'updated_at'        => date('Y-m-d H:i:s'),
                        ];
                    }
                }, $attendances);
    
                $parsed_data = array_values(array_filter($massAssign, function ($data) {
                    return $data !== null;
                }));

                LogAttendance::insert($parsed_data);
            }

            $final_data = DB::table('log_attendances as la')
                ->select(
                    'la.id',
                    'la.personel_id',
                    'la.shift_id',
                    DB::raw('MIN(la.min) as min'),
                    DB::raw('MAX(la.max) as max'),
                    DB::raw("(SELECT coordinate FROM log_attendances WHERE min = MIN(la.min) AND personel_id = la.personel_id LIMIT 1) as coordinate"),
                    DB::raw("(SELECT coordinate_out FROM log_attendances WHERE max = MAX(la.max) AND personel_id = la.personel_id LIMIT 1) as coordinate_out"),
                    DB::raw("(SELECT min_source FROM log_attendances WHERE min = MIN(la.min) AND personel_id = la.personel_id LIMIT 1) as min_source"),
                    DB::raw("(SELECT max_source FROM log_attendances WHERE max = MAX(la.max) AND personel_id = la.personel_id LIMIT 1) as max_source"),
                )
                ->where('la.date', date('Y-m-d'))
                ->groupBy('la.personel_id')
                ->get();

            foreach ($final_data as $f_data) {
                $employee             = Employee::where('is_active', 1)->where('branch_id', 8)->where('personel_id', $f_data->personel_id)->select('id', 'user_id', 'shift_type_id')->with('shift_type:id')->first();
                $duplicate_attendance = AttendanceEmployee::where('employee_id', $employee?->id)->where('date', '=', $date)->where('clock_in', $f_data->min)->where('clock_out', $f_data->max)->first();

                if (empty($duplicate_attendance)) {
                    if (!empty($employee)) {
                        if ($f_data->shift_id) {
                            $shift_times = ShiftTime::where('shift_type_id', $f_data->shift_id)
                                ->where('days',date('l'))
                                ->select(['is_working', 'start_time', 'end_time'])
                                ->first();
                        } else {
                            $shift_times = ShiftTime::where('shift_type_id', $employee->shift_type->id)
                                ->where('days',date('l'))
                                ->select(['is_working', 'start_time', 'end_time'])
                                ->first();
                        }

                        $attendance = AttendanceEmployee::where('employee_id', $employee->id)->where('date', '=', $date)->orderBy('created_at', 'DESC')->first();
                        if ($attendance?->source_out != 'Application' || empty($attendance) || ($attendance?->source_out == 'Application' && $attendance?->clock_in == $attendance?->clock_out)) {
                            $clock_in  = $attendance['clock_in'] ?? null;
                            $clock_out = null;
            
                            if ($f_data->min) {
                                $clock_in = $f_data->min;
                            }
            
                            if ($f_data->max != $f_data->min && !empty($attendance)) {
                                $clock_out = $f_data->max;
                            } else {
                                $clock_out = $f_data->min;
                            }
    
                            // * Calculating late
                            $late = '00:00:00';
                            if (strtotime($clock_in) > (strtotime($shift_times->start_time) + ((int)$settings['late_tolerance'] * 60))) {
                                $totalLateSeconds = strtotime($clock_in) - (strtotime($shift_times->start_time) + ((int)$settings['late_tolerance'] * 60));
                                $late_hours = floor($totalLateSeconds / 3600);
                                $late_mins  = floor($totalLateSeconds / 60 % 60);
                                $late_secs  = floor($totalLateSeconds % 60);
                                $late  = sprintf('%02d:%02d:%02d', $late_hours, $late_mins, $late_secs);
                            }
            
                            // * Calculating Workhours
                            $workhours = '00:00:00';
                            if ($clock_in && $clock_out && strtotime($clock_out) > strtotime($clock_in)) {
                                $total_workhour_seconds = strtotime($clock_out) - strtotime($clock_in);
            
                                $workhour_hours = floor($total_workhour_seconds / 3600);
                                $workhour_mins  = floor($total_workhour_seconds / 60 % 60);
                                $workhour_secs  = floor($total_workhour_seconds % 60);
                                $workhours      = sprintf('%02d:%02d:%02d', $workhour_hours, $workhour_mins, $workhour_secs);
                            }
            
                            // * Calculating Early Leaving
                            $early_leaving = '00:00:00';
                            if (strtotime($clock_out) <= strtotime($shift_times->end_time)) {
                                $total_early_seconds = strtotime($shift_times->end_time) - strtotime($clock_out);
                                $early_hours         = floor($total_early_seconds / 3600);
                                $early_mins          = floor($total_early_seconds / 60 % 60);
                                $early_secs          = floor($total_early_seconds % 60);
                                $early_leaving       = sprintf('%02d:%02d:%02d', $early_hours, $early_mins, $early_secs);
                            }
            
                            // ? Create / Update Attendance
                            if ($attendance && strtotime($clock_in) < strtotime($attendance?->clock_in)) {
                                Log::info('Updating Today Attendance Data');
                                $attendance->clock_in      = $clock_in;
                                $attendance->work_hours    = $shift_times->is_working ? $workhours : '00:00:00';
                                $attendance->overtime      = '00:00:00';
                                $attendance->early_leaving = $shift_times->is_working ? $early_leaving : '00:00:00';
                                $attendance->coord_in      = $f_data->coordinate;
                                $attendance->source_in     = $f_data->min_source;
                                $attendance->save();
                            } elseif ($attendance && $clock_out) {
                                Log::info('Updating Today Attendance Data');
                                $attendance->clock_out     = $clock_out;
                                $attendance->work_hours    = $shift_times->is_working ? $workhours : '00:00:00';
                                $attendance->overtime      = '00:00:00';
                                $attendance->early_leaving = $shift_times->is_working ? $early_leaving : '00:00:00';
                                $attendance->coord_out     = $f_data->coordinate_out;
                                $attendance->source_out    = $f_data->max_source;
                                $attendance->save();
                            } elseif ($clock_in && empty($attendance)) {
                                Log::info('Creating New Attendance Data');
                                $new_attendance                       = new AttendanceEmployee();
                                $new_attendance->employee_id          = $employee->id;
                                $new_attendance->date                 = $date;
                                $new_attendance->attendance_status_id = $presentAttendance->id;
                                $new_attendance->status               = $presentAttendance->name;
                                $new_attendance->clock_in             = $clock_in;
                                $new_attendance->clock_out            = $clock_out;
                                $new_attendance->late                 = $shift_times->is_working ? $late : '00:00:00';
                                $new_attendance->work_hours           = $shift_times->is_working ? $workhours : '00:00:00';
                                $new_attendance->early_leaving        = $shift_times->is_working ? $early_leaving : '00:00:00';
                                $new_attendance->overtime             = '00:00:00';
                                $new_attendance->total_rest           = '00:00:00';
                                $new_attendance->created_by           = $employee->user_id;
                                $new_attendance->attendance_type_id   = 1; //* ON SITE
                                $new_attendance->coord_in             = $f_data->coordinate;
                                $new_attendance->is_valid             = true;
                                $new_attendance->validate_by          = 1; //* System
                                $new_attendance->shift_type_id        = $employee->shift_type_id;
                                $new_attendance->source_in            = $f_data->min_source;
                                $new_attendance->save();
                            }
                        }
                    }
                }
            }

            LogSyncAttendance::insert(['status'=>'Success','date'=>$date,'unit'=>$units[$a],'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
        }
        return null;
    }
}

