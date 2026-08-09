<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TalentJudgingCriterion extends Model
{
    protected $fillable = [
        'talent_event_id',
        'name',
        'max_points',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'max_points' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function talentEvent(): BelongsTo
    {
        return $this->belongsTo(TalentEvent::class);
    }

    public function criterionScores(): HasMany
    {
        return $this->hasMany(TalentJudgeCriterionScore::class, 'criterion_id');
    }
}
