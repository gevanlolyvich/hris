<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleMaintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'maintenance_type_id',
        'name',
        'start_date',
        'end_date',
        'location',
        'cost',
        'file',
        'description'
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id')->withTrashed();
    }

    public function maintenanceType(): BelongsTo
    {
        return $this->belongsTo(VehicleMaintenanceType::class, 'maintenance_type_id', 'id')->withTrashed();
    }
}
