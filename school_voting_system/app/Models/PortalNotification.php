<?php

namespace App\Models;

use App\Enums\NotificationModule;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalNotification extends Model
{
    protected $fillable = [
        'title',
        'message',
        'type',
        'module',
        'user_id',
        'recipient_role',
        'read_at',
        'created_by',
        'announcement_id',
        'related_id',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'module' => NotificationModule::class,
            'recipient_role' => UserRole::class,
        ];
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    /**
     * Recipients only see their own notification rows (no shared broadcast reads).
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query
            ->where('user_id', $user->id)
            ->orderByDesc('created_at');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }
}
