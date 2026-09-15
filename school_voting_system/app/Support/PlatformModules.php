<?php

namespace App\Support;

use App\Enums\AnnouncementAudience;
use App\Models\SystemSetting;
use Illuminate\Support\Carbon;

class PlatformModules
{
    public const ELECTIONS = 'elections';

    public const TALENT = 'talent';

    public const FUNDRAISING = 'fundraising';

    public static function elections(): bool
    {
        return (bool) SystemSetting::getValue('enable_elections', true);
    }

    public static function talent(): bool
    {
        return (bool) SystemSetting::getValue('enable_talent_voting', true);
    }

    public static function fundraising(): bool
    {
        return (bool) SystemSetting::getValue('enable_fundraising', true);
    }

    public static function recovery(): bool
    {
        return (bool) SystemSetting::getValue('two_factor_recovery_enabled', true);
    }

    public static function enabled(string $module): bool
    {
        return match ($module) {
            self::ELECTIONS => self::elections(),
            self::TALENT => self::talent(),
            self::FUNDRAISING => self::fundraising(),
            default => true,
        };
    }

    /**
     * @return list<string>
     */
    public static function announcementDefaultAudiences(): array
    {
        $visibility = (string) SystemSetting::getValue('announcement_default_visibility', 'all');

        return match ($visibility) {
            'students' => [AnnouncementAudience::Students->value],
            'faculty' => [AnnouncementAudience::Faculty->value],
            'admins' => [AnnouncementAudience::Administrators->value],
            default => [AnnouncementAudience::AllUsers->value],
        };
    }

    public static function announcementDefaultExpiresAt(): ?string
    {
        $days = (int) SystemSetting::getValue('announcement_default_expiration_days', 14);

        if ($days < 1) {
            return null;
        }

        return Carbon::now()->addDays($days)->format('Y-m-d\TH:i');
    }

    public static function moduleForRoute(?string $name): ?string
    {
        if (! is_string($name) || $name === '') {
            return null;
        }

        foreach (self::routePrefixes(self::ELECTIONS) as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return self::ELECTIONS;
            }
        }

        foreach (self::routePrefixes(self::TALENT) as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return self::TALENT;
            }
        }

        foreach (self::routePrefixes(self::FUNDRAISING) as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return self::FUNDRAISING;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    protected static function routePrefixes(string $module): array
    {
        return match ($module) {
            self::ELECTIONS => [
                'admin.elections.',
                'admin.campaigns.',
                'admin.candidates.',
                'admin.election.',
                'admin.live.election',
                'admin.results.elections',
                'admin.results.election.',
                'admin.reports.index',
                'admin.export.preliminary',
                'admin.voters.remind',
                'student.voting.',
                'student.campaigns.',
                'student.candidates.',
                'student.results.election.',
                'faculty.elections.',
                'faculty.results.election.',
                'super-admin.elections.',
            ],
            self::TALENT => [
                'admin.talent-competition.',
                'admin.talent-participants.',
                'admin.talent.',
                'admin.live.talent',
                'admin.results.competitions',
                'admin.results.talent.',
                'admin.reports.talent',
                'student.talent-voting.',
                'student.talent-registration.',
                'student.results.talent.',
                'faculty.judging.',
                'faculty.talent.',
                'faculty.results.talent.',
                'talent.video.stream',
            ],
            self::FUNDRAISING => [
                'admin.fundraisers.',
                'admin.reports.fundraising',
                'student.fundraising.',
                'faculty.fundraising.',
            ],
            default => [],
        };
    }
}
