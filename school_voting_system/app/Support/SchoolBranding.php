<?php

namespace App\Support;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Dual branding from System Settings (General):
 * - System name = product (e.g. School Voting System)
 * - School name = institution (e.g. Rosemont Hills Montessori College)
 */
class SchoolBranding
{
    public static function systemName(): string
    {
        $value = static::setting('system_name', 'School Voting System');

        return filled($value) ? (string) $value : 'School Voting System';
    }

    public static function schoolName(): string
    {
        $value = static::setting(
            'school_name',
            'Rosemont Hills Montessori College',
        );

        return filled($value) ? (string) $value : 'Rosemont Hills Montessori College';
    }

    /**
     * @deprecated Prefer systemName() or schoolName() explicitly.
     * Kept as the institution name for older call sites that meant "school".
     */
    public static function name(): string
    {
        return static::schoolName();
    }

    public static function poweredBy(): string
    {
        $school = static::schoolName();

        return filled($school) ? 'Powered by '.$school : '';
    }

    public static function academicYear(): string
    {
        return (string) static::setting(
            'academic_year',
            now()->format('Y').'-'.now()->addYear()->format('Y'),
        );
    }

    public static function semester(): string
    {
        return (string) static::setting('semester', '1st Semester');
    }

    public static function periodLabel(): string
    {
        return trim(static::academicYear().' · '.static::semester());
    }

    public static function logoPath(): ?string
    {
        $path = static::setting('school_logo_path');

        return filled($path) ? (string) $path : null;
    }

    /**
     * Bundled school crest used when no custom logo is uploaded.
     */
    public static function defaultLogoUrl(): string
    {
        return asset('images/rosemont-hills-logo.png');
    }

    /**
     * @param  bool  $withFallback  When true, always return the default crest if no upload exists.
     */
    public static function logoUrl(bool $withFallback = true): ?string
    {
        try {
            $path = static::logoPath();

            if ($path && Storage::disk('public')->exists($path)) {
                return asset('storage/'.ltrim($path, '/'));
            }
        } catch (Throwable) {
            // Fall through to the default crest.
        }

        if (! $withFallback) {
            return null;
        }

        return static::defaultLogoUrl();
    }

    /**
     * Embeddable logo for PDF / print exports.
     */
    public static function logoDataUri(): ?string
    {
        $path = static::logoPath();

        if ($path && Storage::disk('public')->exists($path)) {
            $binary = Storage::disk('public')->get($path);
            $mime = Storage::disk('public')->mimeType($path) ?: 'image/png';

            return 'data:'.$mime.';base64,'.base64_encode($binary);
        }

        $fallback = public_path('images/rosemont-hills-logo.png');

        if (is_file($fallback)) {
            return 'data:image/png;base64,'.base64_encode((string) file_get_contents($fallback));
        }

        return null;
    }

    protected static function setting(string $key, mixed $default = null): mixed
    {
        try {
            return SystemSetting::getValue($key, $default);
        } catch (Throwable) {
            return $default;
        }
    }
}
