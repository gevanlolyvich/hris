<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportObstacle extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'obstacle',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id', 'id')->withTrashed();;
    }
}
