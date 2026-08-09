<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasskeyResetEnrollmentLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $enrollmentUrl,
        public int $expiresInMinutes,
        public ?int $recoveryRequestId = null,
    ) {}

    public function build(): static
    {
        $subject = 'Your passkey reset link';

        if ($this->recoveryRequestId) {
            $subject .= " [Request #{$this->recoveryRequestId}]";
        }

        return $this->subject($subject)
            ->view('emails.passkey-reset-enrollment-link');
    }
}

