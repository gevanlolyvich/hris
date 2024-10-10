<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeePeriod extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'start_period', 'end_period', 'reason', 'active', 'status', 'sync'];
}
