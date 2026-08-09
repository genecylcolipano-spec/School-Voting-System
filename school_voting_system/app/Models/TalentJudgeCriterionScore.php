<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TalentJudgeCriterionScore extends Model
{
    protected $fillable = [
        'score_sheet_id',
        'criterion_id',
        'points',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'decimal:2',
        ];
    }

    public function scoreSheet(): BelongsTo
    {
        return $this->belongsTo(TalentJudgeScoreSheet::class, 'score_sheet_id');
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(TalentJudgingCriterion::class, 'criterion_id');
    }
}
