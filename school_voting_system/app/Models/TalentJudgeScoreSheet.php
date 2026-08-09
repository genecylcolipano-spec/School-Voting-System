<?php

namespace App\Models;

use App\Enums\TalentJudgeScoreStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TalentJudgeScoreSheet extends Model
{
    protected $fillable = [
        'talent_event_id',
        'user_id',
        'talent_event_entry_id',
        'total_score',
        'status',
        'notes',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'total_score' => 'decimal:2',
            'status' => TalentJudgeScoreStatus::class,
            'submitted_at' => 'datetime',
        ];
    }

    public function talentEvent(): BelongsTo
    {
        return $this->belongsTo(TalentEvent::class);
    }

    public function judge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(TalentEventEntry::class, 'talent_event_entry_id');
    }

    public function criterionScores(): HasMany
    {
        return $this->hasMany(TalentJudgeCriterionScore::class, 'score_sheet_id');
    }

    public function isSubmitted(): bool
    {
        return $this->status === TalentJudgeScoreStatus::Submitted;
    }

    public function isLocked(): bool
    {
        return $this->status?->isLocked() ?? false;
    }
}
