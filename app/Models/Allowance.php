<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Allowance extends Model
{
    protected $fillable = [
        'employee_id',
        'allowance_option',
        'title',
        'is_recurring',
        'is_prorated',
        'period',
        'type',
        'amount',
        'created_by',
    ];

    public function employee()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee_id')->first();
    }

    public function allowance_option()
    {
        return $this->hasOne('App\Models\AllowanceOption', 'id', 'allowance_option')->first();
    }
}
