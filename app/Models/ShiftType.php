<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShiftType extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    public function shiftTimes(): HasMany
    {
        return $this->hasMany(ShiftTime::class)->withTrashed();
    }

    public function shift_histories(): HasMany
    {
        return $this->hasMany(ShiftHistory::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function employeeShiftSchedules(): HasMany
    {
        return $this->hasMany(EmployeeShiftSchedule::class);
    }
}
