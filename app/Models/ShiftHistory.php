<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_type_id',
        'employee_id',
    ];

    public function shiftType()
    {
        return $this->belongsTo(ShiftType::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
