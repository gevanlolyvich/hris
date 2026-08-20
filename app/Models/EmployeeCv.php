<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCv extends Model
{
    protected $table = 'employee_cvs';

    protected $fillable = [
        'employee_id',
        'summary',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}