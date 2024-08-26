<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    protected $fillable = [
        'name',
        'trainer_option',
        'training_type',
        'training_cost',
        'employee',
        'start_date',
        'end_date',
        'description',
        'created_by',
    ];

    public static $options = [
        'Internal',
        'External',
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

    public static function status($status)
    {
        if($status=='0')
        {
            return 'Pending';
        }
        if($status=='1')
        {
            return 'Started';
        }
        if($status=="2")
        {
            return "Completed";
        }
        if($status=="3")
        {
            return "Terminated";
        }

    }
}
