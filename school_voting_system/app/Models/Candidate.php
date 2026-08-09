<?php

namespace App\Models;

use App\Exceptions\VoteIntegrityException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    /** @use HasFactory<\Database\Factories\CandidateFactory> */
    use HasFactory;

    protected $fillable = [
        'election_id',
        'election_category_id',
        'user_id',
        'partylist_id',
        'display_name',
        'position',
        'party_or_group',
        'platform',
        'biography',
        'campaign_promises',
        'grade_level',
        'section',
        'photo_path',
        'is_active',
        'eligibility_status',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Candidate $candidate) {
            $candidate->assertCategoryBelongsToElection();
        });
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ElectionCategory::class, 'election_category_id');
    }

    public function partylist(): BelongsTo
    {
        return $this->belongsTo(Partylist::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function voteCount(): int
    {
        return $this->votes()->count();
    }

    public function assertCategoryBelongsToElection(): void
    {
        if (! $this->election_category_id) {
            return;
        }

        $categoryElectionId = ElectionCategory::query()
            ->whereKey($this->election_category_id)
            ->value('election_id');

        if ($categoryElectionId === null) {
            throw new VoteIntegrityException('The selected election category does not exist.');
        }

        if ((int) $categoryElectionId !== (int) $this->election_id) {
            throw new VoteIntegrityException('Candidate category must belong to the same election.');
        }
    }
}
