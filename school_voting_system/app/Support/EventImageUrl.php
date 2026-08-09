<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class EventImageUrl
{
    public const PLACEHOLDER = 'https://images.unsplash.com/photo-1515169067865-5387f8de2b3a?auto=format&fit=crop&w=800&q=80';

    /**
     * Local category cover assets (served from /public).
     *
     * @var array<string, string>
     */
    protected const CATEGORY_COVERS = [
        'election' => 'images/activity-covers/election.svg',
        'talent' => 'images/activity-covers/talent.svg',
        'talent_competition' => 'images/activity-covers/talent.svg',
        'school_event' => 'images/activity-covers/school-event.svg',
        'fundraising' => 'images/activity-covers/fundraising.svg',
    ];

    public static function placeholder(): string
    {
        return self::PLACEHOLDER;
    }

    /**
     * Category-specific default cover for activities without an uploaded banner.
     */
    public static function coverFor(?string $categoryKey = null): string
    {
        $key = strtolower(trim((string) $categoryKey));

        $relative = self::CATEGORY_COVERS[$key]
            ?? self::CATEGORY_COVERS['school_event'];

        return asset($relative);
    }

    /**
     * Prefer an uploaded image URL; otherwise use the category cover.
     */
    public static function uploadedOrCover(?string $uploadedUrl, ?string $categoryKey = null): string
    {
        if (filled($uploadedUrl) && ! self::isLegacyRemotePlaceholder($uploadedUrl)) {
            return $uploadedUrl;
        }

        return self::coverFor($categoryKey);
    }

    public static function isLegacyRemotePlaceholder(?string $url): bool
    {
        if (! filled($url)) {
            return true;
        }

        return str_contains($url, 'photo-1515169067865-5387f8de2b3a');
    }

    public static function resolve(?string $imagePath, ?string $categoryKey = null): string
    {
        if (! filled($imagePath)) {
            return $categoryKey !== null
                ? self::coverFor($categoryKey)
                : self::placeholder();
        }

        $normalized = ltrim($imagePath, '/');

        if (! Storage::disk('public')->exists($normalized)) {
            return $categoryKey !== null
                ? self::coverFor($categoryKey)
                : self::placeholder();
        }

        return asset('storage/'.$normalized);
    }

    public static function hasUploadedImage(?string $imagePath): bool
    {
        if (! filled($imagePath)) {
            return false;
        }

        return Storage::disk('public')->exists(ltrim($imagePath, '/'));
    }
}
