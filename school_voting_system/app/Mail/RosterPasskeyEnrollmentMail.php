<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RosterPasskeyEnrollmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $enrollmentUrl,
        public int $expiresInHours,
    ) {}

    public function build(): static
    {
        return $this->subject('Complete your School Voting System passkey setup')
            ->view('emails.roster-passkey-enrollment');
    }
}
