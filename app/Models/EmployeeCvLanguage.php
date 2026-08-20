<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCvLanguage extends Model
{
    protected $fillable = [
        'employee_id',
        'language',
        'proficiency',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}