<?php

namespace App\Enums;

enum TalentSubmissionMethod: string
{
    case Upload = 'upload';
    case Url = 'url';
    case Both = 'both';

    public function label(): string
    {
        return match ($this) {
            self::Upload => 'Upload Video',
            self::Url => 'Video URL',
            self::Both => 'Upload + URL',
        };
    }

    public function allowsUpload(): bool
    {
        return $this === self::Upload || $this === self::Both;
    }

    public function allowsUrl(): bool
    {
        return $this === self::Url || $this === self::Both;
    }
}
