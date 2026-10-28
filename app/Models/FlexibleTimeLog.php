<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlexibleTimeLog extends Model
{
    public $timestamps = false;

    protected $table = 'flexible_time_logs';

    protected $fillable = [
        'flexible_time_id',
        'from_status',
        'to_status',
        'changed_by',
        'note',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function flexibleTime(): BelongsTo
    {
        return $this->belongsTo(FlexibleTime::class, 'flexible_time_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
