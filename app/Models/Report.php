<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function activities(): HasMany
    {
        return $this->hasMany(ReportActivity::class, 'report_id');
    }

    public function accomplishments(): HasMany
    {
        return $this->hasMany(ReportAccomplishment::class, 'report_id');
    }

    public function obstacles(): HasMany
    {
        return $this->hasMany(ReportObstacle::class, 'report_id');
    }

    public function plans(): HasMany
    {
        return $this->hasMany(ReportPlan::class, 'report_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ReportAttachment::class, 'report_id');
    }

    public static $report_type = [
        'daily'=>'Daily',
        'weekly'=>'Weekly',
        'monthly'=>'Monthly',
        'yearly'=>'Yearly',
    ];
}
