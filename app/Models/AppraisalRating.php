<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppraisalRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'appraisal_id',
        'competency_id',
        'evaluation',
        'rating',
        'score',
    ];

    public function appraisal()
    {
        return $this->belongsTo(Appraisal::class, 'appraisal_id', 'id');
    }

    public function competency()
    {
        return $this->belongsTo(Competencies::class, 'competency_id', 'id');
    }
}
