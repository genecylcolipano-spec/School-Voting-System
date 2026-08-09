<?php

namespace App\Models;

use App\Enums\TalentCategory;
use App\Enums\TalentEntryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class TalentEventEntry extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_WITHDRAWN = 'withdrawn';
    public const STATUS_DISQUALIFIED = 'disqualified';
    public const STATUS_ARCHIVED = 'archived';

    public const SOURCE_ADMIN = 'admin';
    public const SOURCE_SELF = 'self';

    protected $fillable = [
        'talent_event_id',
        'user_id',
        'entry_number',
        'student_id_number',
        'display_name',
        'grade_level',
        'section',
        'course_strand',
        'talent_category',
        'performance_title',
        'profile_summary',
        'performance_description',
        'photo_path',
        'poster_path',
        'video_path',
        'video_url',
        'thumbnail_path',
        'social_media',
        'submitted_at',
        'view_count',
        'source',
        'status',
        'reviewed_by',
        'review_reason',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'talent_category' => TalentCategory::class,
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'view_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Remove uploaded assets when an entry is deleted via the model layer to
        // avoid orphaned files. Videos live on the private disk; images on public.
        static::deleting(function (TalentEventEntry $entry) {
            foreach (['photo_path', 'poster_path', 'thumbnail_path'] as $imageField) {
                $path = $entry->{$imageField};
                if ($path && ! str_starts_with($path, 'http')) {
                    Storage::disk('public')->delete($path);
                }
            }

            if ($entry->video_path && ! str_starts_with($entry->video_path, 'http')) {
                Storage::disk('local')->delete($entry->video_path);
            }
        });
    }

    public function talentEvent(): BelongsTo
    {
        return $this->belongsTo(TalentEvent::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(TalentEventVote::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function statusEnum(): ?TalentEntryStatus
    {
        return TalentEntryStatus::tryFrom((string) $this->status);
    }

    public function statusLabel(): string
    {
        return $this->statusEnum()?->label() ?? ucfirst((string) $this->status);
    }

    public function statusTone(): string
    {
        return $this->statusEnum()?->tone() ?? 'slate';
    }

    public function talentCategoryLabel(): ?string
    {
        return $this->talent_category?->label();
    }

    public function voteCount(): int
    {
        return $this->votes()->count();
    }

    protected function resolveStoredUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    public function posterUrl(): ?string
    {
        return $this->resolveStoredUrl($this->poster_path ?? $this->photo_path);
    }

    public function photoUrl(): ?string
    {
        return $this->resolveStoredUrl($this->photo_path ?? $this->poster_path);
    }

    public function thumbnailUrl(): ?string
    {
        $thumbnail = $this->resolveStoredUrl($this->thumbnail_path);

        return $thumbnail ?? $this->videoThumbnailFromUrl() ?? $this->posterUrl();
    }

    public function hasVideo(): bool
    {
        return filled($this->video_path) || filled($this->video_url);
    }

    /**
     * Streaming URL for uploaded videos (used with a native <video> tag).
     * Uploaded files live on the private disk and are served through an
     * authorization-aware route rather than a public URL.
     */
    public function videoFileUrl(): ?string
    {
        if (! filled($this->video_path)) {
            return null;
        }

        if (str_starts_with($this->video_path, 'http://') || str_starts_with($this->video_path, 'https://')) {
            return $this->video_path;
        }

        return route('talent.video.stream', $this);
    }

    public function videoDownloadUrl(): ?string
    {
        if (! filled($this->video_path) || str_starts_with($this->video_path, 'http')) {
            return null;
        }

        return route('talent.video.stream', ['entry' => $this, 'download' => 1]);
    }

    /**
     * Embeddable URL for external providers (YouTube/Vimeo) suitable for an <iframe>.
     */
    public function videoEmbedUrl(): ?string
    {
        $url = $this->video_url;

        if (! $url) {
            return null;
        }

        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([\w-]{11})~', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return null;
    }

    public function isExternalVideo(): bool
    {
        return filled($this->video_url) && filled($this->videoEmbedUrl());
    }

    public function isDirectVideo(): bool
    {
        return filled($this->video_path);
    }

    protected function videoThumbnailFromUrl(): ?string
    {
        $url = $this->video_url;

        if ($url && preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([\w-]{11})~', $url, $m)) {
            return 'https://img.youtube.com/vi/'.$m[1].'/hqdefault.jpg';
        }

        return null;
    }

    public function incrementViews(): void
    {
        $this->newQuery()->whereKey($this->getKey())->increment('view_count');
    }
}
