<?php

namespace App\Enums;

enum AuditActionType: string
{
    case Auth = 'auth';
    case Election = 'election';
    case Passkey = 'passkey';
    case User = 'user';
    case Backup = 'backup';
    case Security = 'security';
    case Report = 'report';
    case System = 'system';
    case Judging = 'judging';
}
