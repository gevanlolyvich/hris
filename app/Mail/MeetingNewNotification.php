<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MeetingNewNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $meeting;
    public $attendee;

    public function __construct($meeting, $attendee)
    {
        $this->meeting = $meeting;
        $this->attendee = $attendee;
    }

    public function build()
    {
        return $this->view('email.meeting_new_notification')
            ->with('meeting', $this->meeting)
            ->with('attendee', $this->attendee)
            ->subject('Anda diundang ke meeting: ' . $this->meeting->title);
    }
}
