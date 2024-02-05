<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    protected $fillable = [
        'branch',
        'trainer_option',
        'training_type',
        'trainer',
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

    public static $performance = [
        'Not Concluded',
        'Satisfactory',
        'Average',
        'Poor',
        'Excellent',
    ];

    public static $Status = [
        'Pending',
        'Started',
        'Completed',
        'Terminated',
    ];

    public function branches()
    {
        return $this->hasOne('App\Models\Branch', 'id', 'branch');
    }

    public function branch_ref()
    {
        return $this->belongsTo(Branch::class, 'branch', 'id');
    }

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

    public function trainers()
    {
        return $this->hasOne('App\Models\Trainer', 'id', 'trainer');
    }

    public function trainer_ref()
    {
        return $this->belongsTo(Trainer::class, 'trainer', 'id');
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

    public static function performance($performance)
    {
        if($performance == '0')
        {
            return 'Not Concluded';
        } else if($performance == '1')
        {
            return 'Satisfactory';
        } else if($performance == "2")
        {
            return "Average";
        } else if($performance == "3")
        {
            return "Poor";
        } else if($performance == "4")
        {
            return "Excellent";
        }

    }
}
