<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleOfficer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'is_resricted',
        'created_by'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->withTrashed();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id')->withTrashed();
    }

    public function accesses(): HasMany
    {
        return $this->hasMany(VehicleOfficerAccess::class, 'officer_id');
    }
}
