<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceEmployee extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'attendance_status_id',
        'status',
        'clock_in',
        'clock_out',
        'late',
        'early_leaving',
        'work_hours',
        'overtime',
        'total_rest',
        'created_by',
        'attendance_type_id',
        'coord_in',
        'coord_out',
        'is_valid',
        'validate_by'
    ];

    public function employees()
    {
        return $this->hasOne('App\Models\Employee', 'user_id', 'employee_id');
    }

    public function employee()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee_id');
    }

    public function attendanceStatus()
    {
        return $this->belongsTo(AttendanceStatus::class, 'attendance_status_id', 'id');
    }
}
