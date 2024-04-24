<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoalEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'apprisal_id',
        'goal_id',
        'evaluation',
        'weight',
        'rating',
        'score',
    ];

    public function apprisal()
    {
        return $this->belongsTo(Appraisal::class, 'apprisal_id', 'id');
    }

    public function goal()
    {
        return $this->belongsTo(Goal::class, 'goal_id', 'id');
    }
}
