<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelDesignation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'designation_ids',
        'goal_weight',
        'competency_weight',
    ];
}
