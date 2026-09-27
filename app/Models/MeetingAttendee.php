<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingAttendee extends Model
{
    protected $table = 'meeting_attendees';

    protected $fillable = [
        'meeting_new_id',
        'employee_id',
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
