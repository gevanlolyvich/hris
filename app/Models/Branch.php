<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Branch extends Model
{
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'tolerance',
        'created_by',
    ];

    
}
