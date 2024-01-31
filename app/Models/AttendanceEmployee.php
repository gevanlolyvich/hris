<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceEmployee extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'shift_type_id',
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
        'validate_by',
        'note'
    ];

    public function employees()
    {
        return $this->hasOne('App\Models\Employee', 'user_id', 'employee_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function attendanceStatus()
    {
        return $this->belongsTo(AttendanceStatus::class, 'attendance_status_id', 'id');
    }

    public function shift_type()
    {
        return $this->belongsTo(ShiftType::class, 'shift_type_id', 'id');
    }

    public function attendance_type()
    {
        return $this->belongsTo(AttendanceType::class, 'attendance_type_id', 'id');
    }
}
