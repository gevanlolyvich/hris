<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appraisal extends Model
{
    protected $fillable = [
        'employee_id',
        'created_by',
        'start_month',
        'end_month',
        'total_goal_weight',
        'total_goal_score',
        'total_goal_overall',
        'total_competency_weight',
        'total_competency_score',
        'total_competency_overall',
        'total_apprisal',
        'category',
        'comment',
        'created_at',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function indicator()
    {
        return $this->belongsTo(Indicator::class, 'indicator_id', 'id');
    }

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function competency_ratings(): HasMany
    {
        return $this->hasMany(AppraisalRating::class, 'appraisal_id');
    }

    public function goal_evaluations(): HasMany
    {
        return $this->hasMany(GoalEvaluation::class, 'apprisal_id');
    }
}
