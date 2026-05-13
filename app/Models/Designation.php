<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    protected $fillable = [
        'department_id','name','created_by','level_id'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function level()
    {
        return $this->belongsTo(LevelDesignation::class, 'level_id', 'id');
    }
}
