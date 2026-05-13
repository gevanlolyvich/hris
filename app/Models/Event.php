<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'employee_id',
        'title',
        'start_date',
        'end_date',
        'color',
        'description',
        'document',
        'location',
        'location_coord',
        'created_by',
    ];

    public function eventEmployees()
    {
        return $this->hasMany(EventEmployee::class);
    }
}
