<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShiftTime extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = ['id'];

    protected $fillable = [
        'shift_type_id',
        'days',
        'is_working',
        'start_time',
        'end_time',
    ];

    public function shiftType(): BelongsTo
    {
        return $this->belongsTo(ShiftType::class)->withTrashed();
    }
}
