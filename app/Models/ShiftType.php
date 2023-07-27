<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShiftType extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded = ['id'];

    public function shiftTimes(): HasMany
    {
        return $this->hasMany(ShiftTime::class);
    }
}
