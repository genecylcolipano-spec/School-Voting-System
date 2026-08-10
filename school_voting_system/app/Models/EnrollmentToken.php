<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Model;

class EnrollmentToken extends Model
{
    protected $fillable = [
        'token_hash',
        'roster_type',
        'roster_id',
        'account_id',
        'email',
        'first_name',
        'last_name',
        'role',
        'payload',
        'expires_at',
        'used_at',
        'invalidated_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'role' => UserRole::class,
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'invalidated_at' => 'datetime',
        ];
    }

    public function isUsable(): bool
    {
        return $this->used_at === null
            && $this->invalidated_at === null
            && $this->expires_at !== null
            && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function markUsed(): void
    {
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
