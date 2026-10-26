<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MeetingNew extends Model
{
    protected $table = 'meetings_new';

    protected $fillable = [
        'title',
        'meeting_type',
        'meeting_date',
        'meeting_time',
        'document',
        'deadline',
        'created_by',
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'deadline' => 'datetime',
        'document' => 'array',
    ];

    public function attendees()
    {
        return $this->hasMany(MeetingAttendee::class, 'meeting_new_id');
    }

    public function results()
    {
        return $this->hasMany(MeetingResult::class, 'meeting_new_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isLocked()
    {
        return now() > $this->deadline;
    }

    public function isDeleteLocked()
    {
        return now() > $this->deadline;
    }
}
