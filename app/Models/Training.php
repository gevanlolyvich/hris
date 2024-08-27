<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    protected $fillable = [
        'name',
        'organizer',
        'training_type',
        'training_cost',
        'employee',
        'start_date',
        'end_date',
        'description',
        'created_by',
    ];

    public function types()
    {
        return $this->hasOne('App\Models\TrainingType', 'id', 'training_type');
    }

    public function type()
    {
        return $this->belongsTo(TrainingType::class, 'training_type', 'id');
    }

    public function employees()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee');
    }

    public function employee_ref()
    {
        return $this->belongsTo(Employee::class, 'employee', 'id');
    }
}
