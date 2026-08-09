<?php

namespace App\Mail;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AnnouncementPublishedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Announcement $announcement,
        public string $recipientName,
        public string $announcementUrl,
    ) {}

    public function build(): static
    {
        return $this->subject('Announcement: '.$this->announcement->title)
            ->view('emails.announcement-published');
    }
}
