<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Birthdays_greeting extends Model
{
    use HasFactory;

    protected $table = 'birthdays_greeting';

    protected $fillable = [
        'greeting',
    ];
}
