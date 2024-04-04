<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pph21 extends Model
{
    use HasFactory;

    protected $fillable = [
        "employee_id",
        "date",
        "ptkp",
        "tax_object_code",
        "is_gross_up",
        "bruto",
        "rate",
        "pph21",
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
