<?php

namespace App\Enums;

enum PasskeyStatus: string
{
    case Active = 'active';
    case Revoked = 'revoked';
    case Expired = 'expired';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Revoked => 'Revoked',
            self::Expired => 'Expired',
            self::Lost => 'Marked Lost',
        };
    }
}
