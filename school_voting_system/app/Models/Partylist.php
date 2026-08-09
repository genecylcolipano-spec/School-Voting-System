<?php

namespace App\Models;

use App\Enums\CampaignStatus;
use App\Support\ImageDimensions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Partylist extends Model
{
    /** @use HasFactory<\Database\Factories\PartylistFactory> */
    use HasFactory;

    /** @deprecated Use CampaignStatus enum. Retained for backward compatibility. */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'active';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'election_id',
        'name',
        'acronym',
        'color',
        'motto',
        'platform',
        'description',
        'leader',
        'logo_path',
        'banner_path',
        'banner_variants',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => CampaignStatus::class,
            'banner_variants' => 'array',
        ];
    }

    /**
     * Legacy origin election (nullable). Prefer the many-to-many elections().
     */
    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function elections(): BelongsToMany
    {
        return $this->belongsToMany(Election::class, 'election_partylist')->withTimestamps();
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    public function posters(): HasMany
    {
        return $this->hasMany(PartylistPoster::class);
    }

    public function approvedPosters(): HasMany
    {
        return $this->posters()
            ->where('status', PartylistPoster::STATUS_APPROVED)
            ->latest();
    }

    public function isActive(): bool
    {
        return $this->status === CampaignStatus::Active;
    }

    /** @deprecated Use isActive(). */
    public function isPublished(): bool
    {
        return $this->isActive();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CampaignStatus::Active->value);
    }

    /** @deprecated Use scopeActive(). */
    public function scopePublished(Builder $query): Builder
    {
        return $query->active();
    }

    /**
     * Campaigns that can be attached to an election during setup.
     */
    public function scopeSelectableForElections(Builder $query): Builder
    {
        return $query->active();
    }

    /**
     * Students may browse Active campaigns before, during, and after voting.
     */
    public function scopeVisibleToStudents(Builder $query): Builder
    {
        return $query->active();
    }

    /**
     * @return array{width: int, height: int}|null
     */
    public function bannerDimensions(): ?array
    {
        $variants = $this->banner_variants ?? [];

        if (isset($variants['width'], $variants['height'])
            && (int) $variants['width'] > 0
            && (int) $variants['height'] > 0) {
            return [
                'width' => (int) $variants['width'],
                'height' => (int) $variants['height'],
            ];
        }

        if (! filled($this->banner_path)) {
            return null;
        }

        return ImageDimensions::fromStoragePath('public', $this->banner_path);
    }

    public function bannerOrientation(): ?string
    {
        $variants = $this->banner_variants ?? [];

        if (isset($variants['orientation']) && is_string($variants['orientation'])) {
            return $variants['orientation'];
        }

        return ImageDimensions::orientation($this->bannerDimensions());
    }

    public function hasLandscapeBanner(): bool
    {
        return ImageDimensions::isLandscape($this->bannerDimensions());
    }

    public function isPortraitBanner(): bool
    {
        return ImageDimensions::isPortrait($this->bannerDimensions());
    }

    /**
     * Portrait and square banners use contain + blurred fill inside the 16:9 frame.
     */
    public function bannerNeedsContainLayout(): bool
    {
        return ImageDimensions::needsContainLayout($this->bannerDimensions());
    }

    public function hasBanner(): bool
    {
        return filled($this->banner_path);
    }

    public function bannerUrl(): ?string
    {
        if (! $this->hasBanner()) {
            return null;
        }

        return Storage::disk('public')->url($this->banner_path);
    }

    public function bannerMediumUrl(): ?string
    {
        return $this->bannerVariantUrl('medium_path') ?? $this->bannerUrl();
    }

    public function bannerMobileUrl(): ?string
    {
        return $this->bannerVariantUrl('mobile_path') ?? $this->bannerMediumUrl();
    }

    public function landscapeBannerUrl(): ?string
    {
        if (! $this->hasLandscapeBanner()) {
            return null;
        }

        return $this->bannerUrl();
    }

    protected function bannerVariantUrl(string $key): ?string
    {
        $path = ($this->banner_variants ?? [])[$key] ?? null;

        if (! filled($path) || ! Storage::disk('public')->exists(ltrim((string) $path, '/'))) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
