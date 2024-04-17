<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'personel_id',
        'date',
        'coordinate',
        'coordinate_out',
        'min',
        'max',
        'shift_id',
        'min_source',
        'max_source',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'personel_id', 'personel_id');
    }

    public function shift()
    {
        return $this->belongsTo(ShiftType::class, 'shift_id', 'personel_id');
    }
}
