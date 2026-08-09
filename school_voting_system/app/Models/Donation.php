<?php

namespace App\Models;

use App\Exceptions\DonationIntegrityException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Donation extends Model
{
    protected $fillable = [
        'fundraiser_id',
        'user_id',
        'amount',
        'currency',
        'message',
        'is_anonymous',
        'donated_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_anonymous' => 'boolean',
            'donated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Donation $donation) {
            $donation->assertDonationIntegrity();
        });

        static::created(function (Donation $donation) {
            $donation->fundraiser()->increment('amount_raised', $donation->amount);
        });

        static::deleted(function (Donation $donation) {
            $donation->fundraiser()->decrement('amount_raised', $donation->amount);
        });
    }

    public function fundraiser(): BelongsTo
    {
        return $this->belongsTo(Fundraiser::class);
    }

    public function donor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Record a donation atomically and keep fundraiser.amount_raised in sync.
     */
    public static function record(User $donor, Fundraiser $fundraiser, float|string $amount, array $attributes = []): self
    {
        return DB::transaction(function () use ($donor, $fundraiser, $amount, $attributes) {
            return static::create(array_merge($attributes, [
                'fundraiser_id' => $fundraiser->id,
                'user_id' => $donor->id,
                'amount' => $amount,
                'donated_at' => $attributes['donated_at'] ?? now(),
            ]));
        });
    }

    public function assertDonationIntegrity(): void
    {
        if ((float) $this->amount <= 0) {
            throw new DonationIntegrityException('Donation amount must be greater than zero.');
        }

        $donor = $this->donor ?? User::query()->find($this->user_id);
        if (! $donor) {
            throw new DonationIntegrityException('Donor account not found.');
        }

        if (! $donor->canDonate()) {
            throw new DonationIntegrityException('This account is not permitted to make donations.');
        }

        $fundraiser = $this->fundraiser ?? Fundraiser::query()->find($this->fundraiser_id);
        if (! $fundraiser) {
            throw new DonationIntegrityException('Fundraiser not found.');
        }

        if (! $fundraiser->isAcceptingDonations()) {
            throw new DonationIntegrityException('This fundraiser is not currently accepting donations.');
        }

        $amount = (float) $this->amount;
        $min = $fundraiser->minimumDonationAmount();
        if ($amount < $min) {
            throw new DonationIntegrityException('Minimum donation for this campaign is ₱'.number_format($min, 2).'.');
        }

        $max = $fundraiser->maximumDonationAmount();
        if ($max !== null && $amount > $max) {
            throw new DonationIntegrityException('Maximum donation for this campaign is ₱'.number_format($max, 2).'.');
        }

        if ($this->is_anonymous && $fundraiser->allow_anonymous === false) {
            throw new DonationIntegrityException('Anonymous donations are not allowed for this campaign.');
        }
    }
}
