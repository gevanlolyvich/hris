<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Indicator extends Model
{
    protected $fillable = [
        'level_id',
        'created_by',
    ];

    public function level()
    {
        return $this->belongsTo(LevelDesignation::class, 'level_id', 'id');
    }

    public function weights(): HasMany
    {
        return $this->hasMany(IndicatorWeight::class, 'indicator_id');
    }
}
