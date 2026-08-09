<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageDimensions
{
    public const ORIENTATION_LANDSCAPE = 'landscape';

    public const ORIENTATION_PORTRAIT = 'portrait';

    public const ORIENTATION_SQUARE = 'square';

    /**
     * @return array{width: int, height: int}|null
     */
    public static function fromUploadedFile(UploadedFile $file): ?array
    {
        $size = @getimagesize($file->getRealPath());

        if ($size === false) {
            return null;
        }

        return [
            'width' => (int) $size[0],
            'height' => (int) $size[1],
        ];
    }

    /**
     * @return array{width: int, height: int}|null
     */
    public static function fromStoragePath(string $disk, string $path): ?array
    {
        if (! Storage::disk($disk)->exists($path)) {
            return null;
        }

        $size = @getimagesize(Storage::disk($disk)->path($path));

        if ($size === false) {
            return null;
        }

        return [
            'width' => (int) $size[0],
            'height' => (int) $size[1],
        ];
    }

    /**
     * @param  array{width: int, height: int}|null  $dimensions
     */
    public static function orientation(?array $dimensions): ?string
    {
        if ($dimensions === null || $dimensions['width'] < 1 || $dimensions['height'] < 1) {
            return null;
        }

        if ($dimensions['width'] === $dimensions['height']) {
            return self::ORIENTATION_SQUARE;
        }

        return $dimensions['width'] > $dimensions['height']
            ? self::ORIENTATION_LANDSCAPE
            : self::ORIENTATION_PORTRAIT;
    }

    /**
     * @param  array{width: int, height: int}|null  $dimensions
     */
    public static function isLandscape(?array $dimensions): bool
    {
        return self::orientation($dimensions) === self::ORIENTATION_LANDSCAPE;
    }

    /**
     * @param  array{width: int, height: int}|null  $dimensions
     */
    public static function isPortrait(?array $dimensions): bool
    {
        return self::orientation($dimensions) === self::ORIENTATION_PORTRAIT;
    }

    /**
     * @param  array{width: int, height: int}|null  $dimensions
     */
    public static function isSquare(?array $dimensions): bool
    {
        return self::orientation($dimensions) === self::ORIENTATION_SQUARE;
    }

    /**
     * Portrait and square images should use contain + blurred fill in landscape frames.
     *
     * @param  array{width: int, height: int}|null  $dimensions
     */
    public static function needsContainLayout(?array $dimensions): bool
    {
        $orientation = self::orientation($dimensions);

        return $orientation === self::ORIENTATION_PORTRAIT
            || $orientation === self::ORIENTATION_SQUARE;
    }
}
