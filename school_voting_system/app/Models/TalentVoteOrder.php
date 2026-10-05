<?php

namespace App\Models;

use App\Enums\DonationPaymentMethod;
use App\Enums\DonationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TalentVoteOrder extends Model
{
    protected $fillable = [
        'talent_event_id',
        'talent_event_entry_id',
        'user_id',
        'quantity',
        'unit_price',
        'amount',
        'currency',
        'status',
        'payment_method',
        'paymongo_checkout_session_id',
        'paymongo_payment_id',
        'paid_at',
        'votes_credited_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'amount' => 'decimal:2',
            'status' => DonationStatus::class,
            'payment_method' => DonationPaymentMethod::class,
            'paid_at' => 'datetime',
            'votes_credited_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function talentEvent(): BelongsTo
    {
        return $this->belongsTo(TalentEvent::class);
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(TalentEventEntry::class, 'talent_event_entry_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(TalentEventVote::class);
    }

    public function isPaid(): bool
    {
        return $this->status === DonationStatus::Paid;
    }

    public function isPending(): bool
    {
        return $this->status === DonationStatus::Pending;
    }

    public function votesWereCredited(): bool
    {
        return $this->votes_credited_at !== null;
    }
}
