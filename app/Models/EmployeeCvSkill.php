<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCvSkill extends Model
{
    protected $fillable = [
        'employee_id',
        'skill',
        'proficiency',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}