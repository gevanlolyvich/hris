<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bpjs extends Model
{
    protected $fillable = [
        'employee_id',
        'bpjs_option',
        'is_recurring',
        'type',
        'amount',
        'created_by',
    ];

    public function employee()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee_id')->first();
    }

    public function bpjs_option()
    {
        return $this->hasOne('App\Models\BpjsOption', 'id', 'bpjs_option')->first();
    }
}
