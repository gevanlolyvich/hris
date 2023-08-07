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
        'date',
        'start_time',
        'end_time',
        'reason',
        'docs',
        'is_approved',
        'approved_by',
        'created_by',
    ];
}
