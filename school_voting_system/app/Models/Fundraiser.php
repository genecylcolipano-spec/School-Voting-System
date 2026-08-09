<?php

namespace App\Models;

use App\Enums\FundraiserCategory;
use App\Enums\FundraiserStatus;
use App\Enums\FundraiserVisibility;
use App\Support\EventImageUrl;
use App\Support\ImageDimensions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class Fundraiser extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'category',
        'beneficiary',
        'purpose',
        'expected_beneficiaries',
        'goal_amount',
        'amount_raised',
        'min_donation',
        'max_donation',
        'allow_anonymous',
        'generate_receipt',
        'accept_cash',
        'accept_gcash',
        'accept_maya',
        'accept_bank_transfer',
        'banner_path',
        'banner_variants',
        'visibility',
        'is_featured',
        'accept_donations',
        'starts_on',
        'ends_on',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'category' => FundraiserCategory::class,
            'visibility' => FundraiserVisibility::class,
            'goal_amount' => 'decimal:2',
            'amount_raised' => 'decimal:2',
            'min_donation' => 'decimal:2',
            'max_donation' => 'decimal:2',
            'allow_anonymous' => 'boolean',
            'generate_receipt' => 'boolean',
            'accept_cash' => 'boolean',
            'accept_gcash' => 'boolean',
            'accept_maya' => 'boolean',
            'accept_bank_transfer' => 'boolean',
            'is_featured' => 'boolean',
            'accept_donations' => 'boolean',
            'banner_variants' => 'array',
            'starts_on' => 'date',
            'ends_on' => 'date',
            'status' => FundraiserStatus::class,
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function progressPercent(): float
    {
        if ((float) $this->goal_amount <= 0) {
            return 0.0;
        }

        return min(100.0, ((float) $this->amount_raised / (float) $this->goal_amount) * 100);
    }

    public function remainingAmount(): float
    {
        return max(0.0, (float) $this->goal_amount - (float) $this->amount_raised);
    }

    public function daysRemaining(?Carbon $on = null): ?int
    {
        $on ??= now()->startOfDay();

        if (! $this->ends_on) {
            return null;
        }

        if ($on->gt($this->ends_on->copy()->startOfDay())) {
            return 0;
        }

        return (int) $on->diffInDays($this->ends_on->copy()->startOfDay());
    }

    public function donorCount(): int
    {
        if (isset($this->donations_count)) {
            return (int) $this->donations_count;
        }

        if (array_key_exists('unique_donors_count', $this->attributes)) {
            return (int) $this->attributes['unique_donors_count'];
        }

        return (int) $this->donations()->distinct('user_id')->count('user_id');
    }

    /**
     * Lifecycle status derived from dates, goal progress, and admin overrides.
     */
    public function resolvedStatus(?Carbon $on = null): FundraiserStatus
    {
        $on ??= now();
        $stored = $this->status ?? FundraiserStatus::Draft;

        if (in_array($stored, [FundraiserStatus::Cancelled, FundraiserStatus::Archived, FundraiserStatus::Draft], true)) {
            return $stored;
        }

        if ($this->starts_on && $on->toDateString() < $this->starts_on->toDateString()) {
            return FundraiserStatus::Scheduled;
        }

        if ($this->ends_on && $on->toDateString() > $this->ends_on->toDateString()) {
            return FundraiserStatus::Completed;
        }

        if ((float) $this->amount_raised >= (float) $this->goal_amount && (float) $this->goal_amount > 0) {
            return FundraiserStatus::GoalReached;
        }

        return FundraiserStatus::Active;
    }

    public function displayStatusLabel(?Carbon $on = null): string
    {
        return $this->resolvedStatus($on)->label();
    }

    public function isAcceptingDonations(?Carbon $on = null): bool
    {
        $on ??= now();

        if ($this->accept_donations === false) {
            return false;
        }

        if (($this->visibility ?? FundraiserVisibility::Public) === FundraiserVisibility::Hidden) {
            return false;
        }

        $resolved = $this->resolvedStatus($on);

        if (! $resolved->acceptsDonations()) {
            return false;
        }

        if ($this->starts_on && $on->toDateString() < $this->starts_on->toDateString()) {
            return false;
        }

        if ($this->ends_on && $on->toDateString() > $this->ends_on->toDateString()) {
            return false;
        }

        return true;
    }

    public function minimumDonationAmount(): float
    {
        $min = (float) ($this->min_donation ?? 0);

        return $min > 0 ? $min : 1.0;
    }

    public function maximumDonationAmount(): ?float
    {
        $max = $this->max_donation;

        if ($max === null || (float) $max <= 0) {
            return null;
        }

        return (float) $max;
    }

    /**
     * @return array{
     *     total: int,
     *     successful: int,
     *     pending: int,
     *     cancelled: int,
     *     average: float,
     *     largest: float
     * }
     */
    public function donationStatistics(): array
    {
        $query = $this->donations();
        $total = (int) (clone $query)->count();
        $sum = (float) (clone $query)->sum('amount');
        $largest = (float) (clone $query)->max('amount');

        return [
            'total' => $total,
            // Current donations table has no pending/cancelled states — all recorded rows are successful.
            'successful' => $total,
            'pending' => 0,
            'cancelled' => 0,
            'average' => $total > 0 ? round($sum / $total, 2) : 0.0,
            'largest' => $largest,
        ];
    }

    public function bannerUrl(): string
    {
        return EventImageUrl::resolve($this->banner_path);
    }

    public function hasUploadedBanner(): bool
    {
        return EventImageUrl::hasUploadedImage($this->banner_path);
    }

    public function bannerNeedsContainLayout(): bool
    {
        return ImageDimensions::needsContainLayout($this->bannerDimensions());
    }

    public function bannerOrientation(): ?string
    {
        $variants = $this->banner_variants ?? [];

        if (isset($variants['orientation']) && is_string($variants['orientation'])) {
            return $variants['orientation'];
        }

        return ImageDimensions::orientation($this->bannerDimensions());
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

        return ImageDimensions::fromStoragePath('public', (string) $this->banner_path);
    }

    public function bannerMediumUrl(): ?string
    {
        return $this->bannerVariantUrl('medium_path') ?? ($this->hasUploadedBanner() ? $this->bannerUrl() : null);
    }

    public function bannerMobileUrl(): ?string
    {
        return $this->bannerVariantUrl('mobile_path') ?? $this->bannerMediumUrl();
    }

    protected function bannerVariantUrl(string $key): ?string
    {
        $path = ($this->banner_variants ?? [])[$key] ?? null;

        if (! filled($path) || ! Storage::disk('public')->exists(ltrim((string) $path, '/'))) {
            return null;
        }

        return asset('storage/'.ltrim((string) $path, '/'));
    }

    public function scopeAcceptingDonations(Builder $query, ?Carbon $on = null): Builder
    {
        $on ??= now();

        return $query
            ->where('accept_donations', true)
            ->where(function (Builder $q) {
                $q->whereNull('visibility')
                    ->orWhere('visibility', '!=', FundraiserVisibility::Hidden->value);
            })
            ->whereNotIn('status', [
                FundraiserStatus::Draft->value,
                FundraiserStatus::Cancelled->value,
                FundraiserStatus::Archived->value,
            ])
            ->where(function (Builder $query) use ($on) {
                $query->whereNull('starts_on')
                    ->orWhereDate('starts_on', '<=', $on);
            })
            ->where(function (Builder $query) use ($on) {
                $query->whereNull('ends_on')
                    ->orWhereDate('ends_on', '>=', $on);
            });
    }

    public function scopeVisibleToStudents(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('visibility')
                ->orWhere('visibility', FundraiserVisibility::Public->value)
                ->orWhere('visibility', FundraiserVisibility::Private->value);
        })->whereNotIn('status', [
            FundraiserStatus::Draft->value,
            FundraiserStatus::Archived->value,
            FundraiserStatus::Cancelled->value,
        ]);
    }
}
