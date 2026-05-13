<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $fillable = [
        'branch_id',
        'department_id',
        'employee_id',
        'title',
        'meeting_type',
        'url',
        'password',
        'start_time',
        'end_time',
        'note',
        'created_by',
    ];
}
