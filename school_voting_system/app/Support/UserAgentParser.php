<?php

namespace App\Support;

class UserAgentParser
{
    /**
     * @return array{browser: string, os: string, device: string}
     */
    public static function parse(?string $userAgent): array
    {
        $ua = trim((string) $userAgent);

        if ($ua === '') {
            return [
                'browser' => 'Unknown',
                'os' => 'Unknown',
                'device' => 'Unknown',
            ];
        }

        return [
            'browser' => self::browser($ua),
            'os' => self::operatingSystem($ua),
            'device' => self::deviceType($ua),
        ];
    }

    protected static function browser(string $ua): string
    {
        return match (true) {
            str_contains($ua, 'Edg/') => 'Microsoft Edge',
            str_contains($ua, 'OPR/') || str_contains($ua, 'Opera') => 'Opera',
            str_contains($ua, 'Chrome/') && ! str_contains($ua, 'Edg/') => 'Chrome',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Safari/') && ! str_contains($ua, 'Chrome/') => 'Safari',
            default => 'Unknown browser',
        };
    }

    protected static function operatingSystem(string $ua): string
    {
        return match (true) {
            str_contains($ua, 'Windows NT 10') || str_contains($ua, 'Windows NT 11') => 'Windows',
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Mac OS X') || str_contains($ua, 'Macintosh') => 'macOS',
            str_contains($ua, 'Linux') => 'Linux',
            default => 'Unknown OS',
        };
    }

    protected static function deviceType(string $ua): string
    {
        return match (true) {
            str_contains($ua, 'iPad') || str_contains($ua, 'Tablet') => 'Tablet',
            str_contains($ua, 'Mobile') || str_contains($ua, 'iPhone') || str_contains($ua, 'Android') => 'Mobile',
            default => 'Desktop',
        };
    }

    public static function platformFromDeviceName(?string $name): string
    {
        $value = strtolower((string) $name);

        return match (true) {
            str_contains($value, 'iphone') || str_contains($value, 'ipad') || str_contains($value, 'ios') => 'iOS',
            str_contains($value, 'android') => 'Android',
            str_contains($value, 'mac') || str_contains($value, 'macos') => 'macOS',
            str_contains($value, 'windows') || str_contains($value, 'pc') || str_contains($value, 'laptop') => 'Windows',
            str_contains($value, 'linux') => 'Linux',
            default => 'Unknown',
        };
    }

    public static function deviceTypeFromName(?string $name): string
    {
        $value = strtolower((string) $name);

        return match (true) {
            str_contains($value, 'iphone') || str_contains($value, 'android') || str_contains($value, 'phone') => 'Mobile',
            str_contains($value, 'ipad') || str_contains($value, 'tablet') => 'Tablet',
            str_contains($value, 'yubikey') || str_contains($value, 'security key') || str_contains($value, 'usb') => 'Security Key',
            default => 'Desktop / Authenticator',
        };
    }

    public static function browserFromDeviceName(?string $name): string
    {
        $value = strtolower((string) $name);

        return match (true) {
            str_contains($value, 'edge') => 'Microsoft Edge',
            str_contains($value, 'firefox') => 'Firefox',
            str_contains($value, 'chrome') => 'Chrome',
            str_contains($value, 'safari') || str_contains($value, 'iphone') || str_contains($value, 'ipad') => 'Safari',
            default => 'Platform authenticator',
        };
    }
}
