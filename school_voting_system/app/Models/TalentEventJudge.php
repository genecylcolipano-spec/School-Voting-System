<?php

namespace App\Models;

use App\Enums\TalentJudgeAssignmentStatus;
use App\Enums\TalentJudgeRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TalentEventJudge extends Model
{
    protected $fillable = [
        'talent_event_id',
        'user_id',
        'assigned_by',
        'judge_role',
        'status',
        'removal_reason',
        'removed_at',
        'removed_by',
        'assigned_at',
    ];

    protected function casts(): array
    {
        return [
            'judge_role' => TalentJudgeRole::class,
            'status' => TalentJudgeAssignmentStatus::class,
            'assigned_at' => 'datetime',
            'removed_at' => 'datetime',
        ];
    }

    public function talentEvent(): BelongsTo
    {
        return $this->belongsTo(TalentEvent::class);
    }

    /** Alias for competition_judges naming in product docs. */
    public function competition(): BelongsTo
    {
        return $this->talentEvent();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function faculty(): BelongsTo
    {
        return $this->user();
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function remover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'removed_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', TalentJudgeAssignmentStatus::Active);
    }

    public function isActive(): bool
    {
        return $this->status === TalentJudgeAssignmentStatus::Active;
    }

    public function roleLabel(): string
    {
        return $this->judge_role?->label() ?? TalentJudgeRole::Judge->label();
    }

    public function statusLabel(): string
    {
        return $this->status?->label() ?? TalentJudgeAssignmentStatus::Active->label();
    }
}
