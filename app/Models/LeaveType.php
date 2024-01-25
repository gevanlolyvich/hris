<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = [
        'title',
        'is_active',
        'days',
        'created_by',
    ];
}
