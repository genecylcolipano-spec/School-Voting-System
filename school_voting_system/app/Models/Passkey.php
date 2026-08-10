<?php

namespace App\Models;

use App\Enums\PasskeyStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Passkeys\Passkey as BasePasskey;

/**
 * Passkey credential bound to a user device.
 *
 * @property int $counter
 * @property string|null $public_key
 * @property string|null $device_name
 */
class Passkey extends BasePasskey
{
    protected $fillable = [
        'name',
        'device_name',
        'credential_id',
        'credential',
        'public_key',
        'counter',
        'status',
        'expires_at',
        'revoked_at',
        'revoked_by',
        'reassigned_to_user_id',
        'marked_lost_at',
    ];

    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'counter' => 'integer',
            'status' => PasskeyStatus::class,
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
            'marked_lost_at' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    public function revoker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    public function reassignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reassigned_to_user_id');
    }

    public function isUsable(): bool
    {
        if ($this->status !== PasskeyStatus::Active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return $this->revoked_at === null && $this->marked_lost_at === null;
    }

    /**
     * Revoke every usable passkey for the user except the newly registered one.
     * Used after a successful passkey reset / bootstrap enrollment.
     */
    public static function revokeOthersForUser(User $user, int $keepPasskeyId, ?int $revokedByUserId = null): int
    {
        $ids = static::query()
            ->where('user_id', $user->id)
            ->where('id', '!=', $keepPasskeyId)
            ->whereNull('revoked_at')
            ->pluck('id');

        if ($ids->isEmpty()) {
            return 0;
        }

        return (int) static::query()
            ->whereIn('id', $ids)
            ->update([
                'status' => PasskeyStatus::Revoked->value,
                'revoked_at' => now(),
                'revoked_by' => $revokedByUserId ?? $user->id,
                'updated_at' => now(),
            ]);
    }

    public function owner(): BelongsTo
    {
        return $this->user();
    }

    /**
     * Sync denormalized security columns from the WebAuthn credential payload.
     *
     * @param  array<string, mixed>  $credential
     */
    public function syncSecurityMetadataFromCredential(array $credential): void
    {
        $this->counter = (int) ($credential['counter'] ?? $this->counter ?? 0);
        $this->public_key = isset($credential['publicKey'])
            ? json_encode($credential['publicKey'], JSON_THROW_ON_ERROR)
            : $this->public_key;

        if ($this->device_name) {
            $this->name = $this->device_name;
        } elseif ($this->name && ! $this->device_name) {
            $this->device_name = $this->name;
        }
    }
}
