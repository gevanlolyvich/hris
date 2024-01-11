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
        'document_path',
        'transfer_date',
        'description',
        'created_by',
    ];

    public function department()
    {
        return $this->belongsTo('App\Models\Department', 'department_id', 'id');
    }

    public function branch()
    {
        return $this->hasMany('App\Models\Branch', 'id', 'branch_id')->first();
    }

    public function designation()
    {
        return $this->belongsTo('App\Models\Designation', 'designation_id', 'id');
    }

    public function managed()
    {
        return $this->belongsTo('App\Models\Employee', 'managed_by', 'id');
    }


    public function employee()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee_id')->first();
    }
}
