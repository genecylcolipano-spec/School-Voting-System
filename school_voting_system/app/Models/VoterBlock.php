<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoterBlock extends Model
{
    protected $fillable = [
        'user_id',
        'election_id',
        'blocked_by',
        'reason',
        'notified_super_admin',
        'blocked_at',
        'unblocked_at',
    ];

    protected function casts(): array
    {
        return [
            'notified_super_admin' => 'boolean',
            'blocked_at' => 'datetime',
            'unblocked_at' => 'datetime',
        ];
    }

    public function voter(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function blocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function isActive(): bool
    {
        return $this->unblocked_at === null;
    }
}
