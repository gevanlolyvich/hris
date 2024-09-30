<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'status',
        'type',
        'police_no',
        'km',
        'emoney_balance',
        'branch_id',
        'version'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function lendings(): HasMany
    {
        return $this->hasMany(VehicleLending::class, 'vehicle_id');
    }

    public function incrementVersion()
    {
        $this->version++;
        $this->save();
    }

    public static function getVehicleStatuses()
    {
        return [
            'active' => __('Active'),
            'inactive' => __('Inactive'),
            'under maintenance' => __('Under Maintenance')
        ];
    }
}
