<?php

namespace App\Models;

use App\Services\Student\StudentBallotCatalog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElectionCategory extends Model
{
    /** @use HasFactory<\Database\Factories\ElectionCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'election_id',
        'name',
        'slug',
        'sort_order',
        'max_selections',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'max_selections' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        $forget = static function (ElectionCategory $category): void {
            StudentBallotCatalog::forget($category->election_id);
        };

        static::saved($forget);
        static::deleted($forget);
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    public function activeCandidates(): HasMany
    {
        return $this->candidates()->where('is_active', true);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }
}
