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
        'note',
        'source_in',
        'source_out'
    ];

    public function employees()
    {
        return $this->hasOne('App\Models\Employee', 'user_id', 'employee_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id')->withTrashed();
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

    public function getDateRange()
    {
        if ($this->clock_in && $this->clock_out && $this->clock_in !== '00:00:00' && $this->clock_out !== '00:00:00') {
            if (strtotime($this->clock_out) < strtotime($this->clock_in)) {
                $endDate = date('Y-m-d', strtotime($this->date . ' +1 day'));
                return \Auth::user()->dateFormat($this->date) . ' - ' . \Auth::user()->dateFormat($endDate);
            }
        }
        return \Auth::user()->dateFormat($this->date);
    }
}
