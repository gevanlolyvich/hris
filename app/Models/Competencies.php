<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Competencies extends Model
{
    protected $fillable = [
        'name',
        'type',
        'created_by',
        'performance_type_id',
        'description'
    ];

    public static $types = [
        'Grade' => 'Grade',
        'Essay' => 'Essay'
    ];

    public function performance_type()
    {
        return $this->belongsTo(Performance_Type::class, 'performance_type_id', 'id');
    }
}
