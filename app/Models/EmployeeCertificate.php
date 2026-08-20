<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCertificate extends Model
{
    protected $fillable = [
        'employee_id',
        'name',
        'issuer',
        'issue_date',
        'expiry_date',
        'description',
        'file',
        'created_by',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}