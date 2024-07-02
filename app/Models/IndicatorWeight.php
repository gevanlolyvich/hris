<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndicatorWeight extends Model
{
    use HasFactory;

    protected $fillable = [
        'indicator_id',
        'competency_id',
        'weight',
    ];

    public function indicator()
    {
        return $this->belongsTo(Indicator::class, 'indicator_id', 'id');
    }

    public function competency()
    {
        return $this->belongsTo(Competencies::class, 'competency_id', 'id');
    }
}
