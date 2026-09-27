<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingResult extends Model
{
    protected $table = 'meeting_results';

    protected $fillable = [
        'meeting_new_id',
        'employee_id',
        'content',
        'filled_at',
    ];

    protected $casts = [
        'filled_at' => 'datetime',
    ];

    public function meeting()
    {
        return $this->belongsTo(MeetingNew::class, 'meeting_new_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
