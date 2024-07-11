<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthyStep extends Model
{
    use HasFactory;

    public function healthy_target(): BelongsTo
    {
        return $this->belongsTo(HealthyTarget::class, 'target_id');
    }
}
