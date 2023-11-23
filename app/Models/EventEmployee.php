<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventEmployee extends Model
{
    protected $fillable = [
        'event_id',
        'employee_id',
        'created_by',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
