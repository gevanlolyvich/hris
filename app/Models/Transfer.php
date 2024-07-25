<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = [
        'employee_id',
        'branch_id',
        'department_id',
        'designation_id',
        'managed_by',
        'shift_type_id',
        'document_path',
        'transfer_date',
        'description',
        'created_by',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
    
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id', 'id');
    }

    public function managed()
    {
        return $this->belongsTo('App\Models\Employee', 'managed_by', 'id');
    }

    public function shift()
    {
        return $this->belongsTo(ShiftType::class, 'shift_type_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
