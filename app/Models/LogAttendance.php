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
        'min',
        'max',
        'min_source',
        'max_source',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'personel_id', 'personel_id');
    }
}
