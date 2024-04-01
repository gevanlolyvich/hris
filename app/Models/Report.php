<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'created_by',
        'type',
        'start_date',
        'end_date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id')->withTrashed();;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id')->withTrashed();;
    }

    public static $report_type = [
        'daily'=>'Daily',
        'weekly'=>'Weekly',
        'monthly'=>'Monthly',
        'yearly'=>'Yearly',
    ];
}
