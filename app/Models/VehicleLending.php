<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleLending extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_by',
        'vehicle_id',
        'date',
        'purpose',
        'status',
        'approved_by',
        'pickup_time',
        'return_time',
        'pickup_km',
        'return_km',
        'pickup_file',
        'return_file',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'request_by', 'id')->withTrashed();
    }
    
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id')->withTrashed();
    }
    
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id')->withTrashed();
    }

}
