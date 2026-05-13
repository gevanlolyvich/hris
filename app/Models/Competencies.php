<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competencies extends Model
{
    protected $fillable = [
        'name',
        'type',
        'created_by',
        'performance_type_id',
        'description'
    ];

    public static $types = [
        'Grade' => 'Grade',
        'Essay' => 'Essay'
    ];

    public function performance_type()
    {
        return $this->belongsTo(Performance_Type::class, 'performance_type_id', 'id');
    }

    public function weights(): HasMany
    {
        return $this->hasMany(IndicatorWeight::class, 'competency_id');
    }

    public function Appraisals(): HasMany
    {
        return $this->hasMany(AppraisalRating::class, 'competency_id');
    }
}
