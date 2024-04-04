<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'date',
        'activity',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id', 'id')->withTrashed();;
    }
}
