<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $fillable = [
        'employee_id',
        'title',
        'is_recurring',
        'period',
        'amount',
        'type',
        'created_by',
    ];

    public function employee()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee_id')->first();
    }

    public static $commissiontype = [
        'fixed'=>'Fixed',
        'percentage'=> 'Percentage',
    ];
}
