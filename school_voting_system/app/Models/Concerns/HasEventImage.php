<?php

namespace App\Models\Concerns;

use App\Support\EventImageUrl;
use App\Support\ImageDimensions;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

trait HasEventImage
{
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => EventImageUrl::resolve($this->image_path));
    }

    protected function hasUploadedImage(): Attribute
    {
        return Attribute::get(fn () => EventImageUrl::hasUploadedImage($this->image_path));
    }

    /**
     * @return array{width: int, height: int}|null
     */
    public function imageDimensions(): ?array
    {
        $variants = $this->imageVariantsArray();

        if (isset($variants['width'], $variants['height'])
            && (int) $variants['width'] > 0
            && (int) $variants['height'] > 0) {
            return [
                'width' => (int) $variants['width'],
                'height' => (int) $variants['height'],
            ];
        }

        if (! filled($this->image_path)) {
            return null;
        }

        return ImageDimensions::fromStoragePath('public', (string) $this->image_path);
    }

    public function imageOrientation(): ?string
    {
        $variants = $this->imageVariantsArray();

        if (isset($variants['orientation']) && is_string($variants['orientation'])) {
            return $variants['orientation'];
        }

        return ImageDimensions::orientation($this->imageDimensions());
    }

    public function isPortraitBannerImage(): bool
    {
        return $this->imageOrientation() === ImageDimensions::ORIENTATION_PORTRAIT;
    }

    public function isSquareBannerImage(): bool
    {
        return $this->imageOrientation() === ImageDimensions::ORIENTATION_SQUARE;
    }

    public function isLandscapeBannerImage(): bool
    {
        return $this->imageOrientation() === ImageDimensions::ORIENTATION_LANDSCAPE;
    }

    /**
     * Portrait/square banners use contain + blurred fill so content is not cropped.
     */
    public function bannerNeedsContainLayout(): bool
    {
        return ImageDimensions::needsContainLayout($this->imageDimensions());
    }

    public function bannerMediumUrl(): ?string
    {
        return $this->variantUrl('medium_path') ?? $this->image_url;
    }

    public function bannerMobileUrl(): ?string
    {
        return $this->variantUrl('mobile_path') ?? $this->bannerMediumUrl();
    }

    public function bannerThumbUrl(): ?string
    {
        return $this->variantUrl('thumb_path');
    }

    /**
     * @return array<string, mixed>
     */
    protected function imageVariantsArray(): array
    {
        $raw = $this->image_variants ?? null;

        if (is_array($raw)) {
            return $raw;
        }

        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    protected function variantUrl(string $key): ?string
    {
        $path = $this->imageVariantsArray()[$key] ?? null;

        if (! filled($path)) {
            return null;
        }

        if (! Storage::disk('public')->exists(ltrim((string) $path, '/'))) {
            return null;
        }

        return asset('storage/'.ltrim((string) $path, '/'));
    }
}
