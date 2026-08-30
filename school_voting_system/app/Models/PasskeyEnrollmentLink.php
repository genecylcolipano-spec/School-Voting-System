<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasskeyEnrollmentLink extends Model
{
    protected $fillable = [
        'user_id',
        'issued_by',
        'token_hash',
        'expires_at',
        'used_at',
        'invalidated_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'invalidated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function isUsable(): bool
    {
        return $this->used_at === null
            && $this->invalidated_at === null
            && $this->expires_at !== null
            && $this->expires_at->isFuture();
    }

    public function markUsed(): void
    {
        if ($this->used_at !== null) {
            return;
        }

        $this->forceFill(['used_at' => now()])->save();
    }

    public function invalidate(): void
    {
        if ($this->invalidated_at !== null || $this->used_at !== null) {
            return;
        }

        $this->forceFill(['invalidated_at' => now()])->save();
    }
}
