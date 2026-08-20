<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCvEducation extends Model
{
    protected $table = 'employee_cv_educations';

    protected $fillable = [
        'employee_id',
        'institution',
        'degree',
        'field_of_study',
        'start_year',
        'end_year',
        'gpa',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}