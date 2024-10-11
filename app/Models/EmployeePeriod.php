<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePeriod extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'start_period', 'end_period', 'reason', 'active', 'status', 'sync', 'response'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
