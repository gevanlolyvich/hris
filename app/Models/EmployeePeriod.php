<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePeriod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'type_id',
        'start_period',
        'end_period',
        'reason',
        'active',
        'status',
        'sync',
        'response'
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(EmployeeType::class, 'type_id', 'id');
    }
}
