<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCvExperience extends Model
{
    protected $fillable = [
        'employee_id',
        'company',
        'position',
        'start_date',
        'end_date',
        'description',
        'sort_order',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}