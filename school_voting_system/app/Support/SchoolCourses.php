<?php

namespace App\Support;

final class SchoolCourses
{
    public const BSIT = 'BSIT';

    public const BSCRIM = 'BSCRIM';

    public const BEED = 'BEED';

    public const BSOA = 'BSOA';

    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        return [
            self::BSIT,
            self::BSCRIM,
            self::BEED,
            self::BSOA,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::BSIT => 'BSIT',
            self::BSCRIM => 'BSCRIM',
            self::BEED => 'BEED',
            self::BSOA => 'BSOA',
        ];
    }

    public static function normalize(?string $value): ?string
    {
        $compact = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', (string) $value));

        if ($compact === '') {
            return null;
        }

        $aliases = [
            'BSIT' => self::BSIT,
            'BSCRIM' => self::BSCRIM,
            'CRIM' => self::BSCRIM,
            'BEED' => self::BEED,
            'BSOA' => self::BSOA,
        ];

        return $aliases[$compact] ?? null;
    }
}
