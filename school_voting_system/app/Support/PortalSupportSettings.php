<?php

namespace App\Support;

use App\Models\SystemSetting;

class PortalSupportSettings
{
    public static function email(): string
    {
        return (string) SystemSetting::getValue(
            'support_email',
            config('mail.from.address', 'ictsupport@school.edu'),
        );
    }

    public static function teamLabel(): string
    {
        return (string) SystemSetting::getValue('support_team_label', 'ICT Support Team');
    }
}
