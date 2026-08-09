<?php

namespace App\Enums;

enum ElectionStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Closed = 'closed';
    case Archived = 'archived';

    public function isOpenForVoting(): bool
    {
        return $this === self::Active;
    }
}
