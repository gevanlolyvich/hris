<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = [
        'employee_id',
        'shift_id',
        'date',
        'start_time',
        'end_time',
        'reason',
        'docs',
        'is_approved',
        'approved_by',
        'created_by',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function shift()
    {
        return $this->belongsTo(ShiftType::class, 'shift_id', 'id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }
}
