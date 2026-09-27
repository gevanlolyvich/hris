<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'branch_id',
        'department_id',
        'jenis',
        'nominal',
        'tanggal',
        'file',
        'created_by',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'tanggal' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFileUrlAttribute()
    {
        return $this->file ? asset('storage/' . $this->file) : null;
    }
}
