<?php

namespace App\Models;

use App\Enums\DonationPaymentMethod;
use App\Enums\DonationStatus;
use App\Exceptions\DonationIntegrityException;
use Illuminate\Database\Eloquent\Builder;
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
        'status',
        'payment_method',
        'paymongo_checkout_session_id',
        'paymongo_payment_id',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_anonymous' => 'boolean',
            'donated_at' => 'datetime',
            'paid_at' => 'datetime',
            'status' => DonationStatus::class,
            'payment_method' => DonationPaymentMethod::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Donation $donation) {
            $donation->status ??= DonationStatus::Pending;
            $donation->assertDonationIntegrity();
        });

        static::deleted(function (Donation $donation) {
            if ($donation->status === DonationStatus::Paid) {
                $donation->fundraiser()->decrement('amount_raised', $donation->amount);
            }
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

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', DonationStatus::Paid);
    }

    public function isPaid(): bool
    {
        return $this->status === DonationStatus::Paid;
    }

    public function isPending(): bool
    {
        return $this->status === DonationStatus::Pending;
    }

    /**
     * Create a pending donation. amount_raised is incremented only after markPaid().
     */
    public static function record(User $donor, Fundraiser $fundraiser, float|string $amount, array $attributes = []): self
    {
        return DB::transaction(function () use ($donor, $fundraiser, $amount, $attributes) {
            return static::create(array_merge([
                'status' => DonationStatus::Pending,
                'currency' => 'PHP',
            ], $attributes, [
                'fundraiser_id' => $fundraiser->id,
                'user_id' => $donor->id,
                'amount' => $amount,
                'donated_at' => $attributes['donated_at'] ?? now(),
            ]));
        });
    }

    /**
     * Mark the donation paid and add it to the campaign total. Idempotent.
     *
     * @return bool True when this call newly counted the donation.
     */
    public function markPaid(?string $paymentId = null, ?string $checkoutSessionId = null): bool
    {
        return DB::transaction(function () use ($paymentId, $checkoutSessionId) {
            $locked = static::query()->whereKey($this->id)->lockForUpdate()->first();

            if (! $locked) {
                return false;
            }

            if ($locked->status === DonationStatus::Paid) {
                if ($paymentId && ! $locked->paymongo_payment_id) {
                    $locked->forceFill(['paymongo_payment_id' => $paymentId])->save();
                }

                return false;
            }

            $locked->forceFill([
                'status' => DonationStatus::Paid,
                'paid_at' => now(),
                'paymongo_payment_id' => $paymentId ?? $locked->paymongo_payment_id,
                'paymongo_checkout_session_id' => $checkoutSessionId ?? $locked->paymongo_checkout_session_id,
            ])->save();

            $locked->fundraiser()->increment('amount_raised', $locked->amount);

            $this->refresh();

            return true;
        });
    }

    public function markFailed(): void
    {
        if ($this->status === DonationStatus::Paid) {
            return;
        }

        $this->forceFill(['status' => DonationStatus::Failed])->save();
    }

    public function markCancelled(): void
    {
        if ($this->status === DonationStatus::Paid) {
            return;
        }

        $this->forceFill(['status' => DonationStatus::Cancelled])->save();
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

        $method = $this->payment_method;
        if ($method instanceof DonationPaymentMethod && ! $method->isAcceptedBy($fundraiser)) {
            throw new DonationIntegrityException('This campaign does not accept '.$method->label().' payments.');
        }
    }
}
