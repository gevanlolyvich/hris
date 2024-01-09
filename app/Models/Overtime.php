<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Overtime extends Model
{
    protected $fillable = [
        'employee_id',
        'title',
        'date',
        'type',
        'is_work_day',
        'clock_in',
        'clock_out',
        'coord_in',
        'coord_out',
        'pict_in',
        'pict_out',
        'description',
        'document',
        'report_document',
        'report_note',
        'created_by',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public static $Overtimetype =[
        'hourly'=>'Hourly',
        'daily'=> 'Daily',
    ];
}
