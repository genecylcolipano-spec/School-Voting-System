<?php

namespace App\Models;

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
