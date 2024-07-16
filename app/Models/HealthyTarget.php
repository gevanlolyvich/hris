<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HealthyTarget extends Model
{
    use HasFactory;

    public function healthy_steps(): HasMany
    {
        return $this->hasMany(HealthyStep::class, 'target_id');
    }
}
