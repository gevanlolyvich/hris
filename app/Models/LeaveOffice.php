<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveOffice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'date',
        'superior_approval_by',
        'hr_approval_by',
        'superior_note',
        'hr_note',
        'leave',
        'return',
        'return_coord',
        'return_pict',
        'status',
        'location',
        'purpose',
        'description'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id')->withTrashed();
    }

    public function superior()
    {
        return $this->belongsTo(Employee::class, 'superior_approval_by', 'id')->withTrashed();
    }

    public function hr()
    {
        return $this->belongsTo(User::class, 'hr_approval_by', 'id')->withTrashed();
    }

    public static $status = [
        'Pending'=>'Pending',
        'Waiting Approval' => 'Waiting Approval',
        'Rejected By Superior'=> 'Rejected By Superior',
        'Waiting HR'=> 'Waiting HR',
        'Rejected By HR'=> 'Rejected By HR',
        'Approved' => 'Approved'
    ];
}
