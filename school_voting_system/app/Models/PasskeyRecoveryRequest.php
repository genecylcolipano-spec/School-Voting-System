<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasskeyRecoveryRequest extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_RESOLVED = 'resolved';

    protected $fillable = [
        'user_id',
        'account_id',
        'email',
        'token_hash',
        'expires_at',
        'used_at',
        'invalidated_at',
        'status',
        'resolved_by',
        'resolved_at',
        'requested_ip',
        'requested_user_agent',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'invalidated_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function isTokenUsable(): bool
    {
        return $this->token_hash !== null
            && $this->used_at === null
            && $this->invalidated_at === null
            && $this->expires_at !== null
            && $this->expires_at->isFuture()
            && $this->user_id !== null;
    }

    public function tokenFailureReason(): ?string
    {
        if ($this->token_hash === null) {
            return 'invalid';
        }

        if ($this->used_at !== null || $this->invalidated_at !== null) {
            return 'used';
        }

        if ($this->expires_at === null || $this->expires_at->isPast()) {
            return 'expired';
        }

        if ($this->user_id === null) {
            return 'invalid';
        }

        return null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
