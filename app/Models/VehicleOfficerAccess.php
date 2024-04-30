<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleOfficerAccess extends Model
{
    use HasFactory;

    protected $fillable = [
        'officer_id',
        'branch_id',
    ];

    public function officer()
    {
        return $this->belongsTo(VehicleOfficer::class, 'officer_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }
}
