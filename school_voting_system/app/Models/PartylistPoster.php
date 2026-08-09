<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartylistPoster extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'partylist_id',
        'election_id',
        'title',
        'file_path',
        'description',
        'status',
        'reviewed_by',
        'review_reason',
        'submitted_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function partylist(): BelongsTo
    {
        return $this->belongsTo(Partylist::class);
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function getFileUrlAttribute(): ?string
    {
        if (! filled($this->file_path)) {
            return null;
        }

        return \App\Support\EventImageUrl::resolve($this->file_path);
    }

    public function hasUploadedFile(): bool
    {
        return \App\Support\EventImageUrl::hasUploadedImage($this->file_path);
    }
}
