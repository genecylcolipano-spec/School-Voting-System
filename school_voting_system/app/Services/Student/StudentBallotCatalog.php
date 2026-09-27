<?php

namespace App\Services\Student;

use App\Models\Election;
use App\Support\EventImageUrl;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class StudentBallotCatalog
{
    public const TTL_SECONDS = 90;

    public static function cacheKey(int $electionId): string
    {
        return 'student.ballot.catalog.'.$electionId;
    }

    public static function forget(?int $electionId): void
    {
        if (! $electionId) {
            return;
        }

        Cache::forget(self::cacheKey($electionId));
    }

    /**
     * Shared ballot cards for an election. Vote locks are applied per student.
     *
     * @param  array<int|string, mixed>  $existingVotes
     * @return list<array<string, mixed>>
     */
    public function categoriesFor(Election $election, $existingVotes = []): array
    {
        $categories = Cache::remember(
            self::cacheKey((int) $election->id),
            self::TTL_SECONDS,
            fn () => $this->buildSharedCategories($election),
        );

        $votes = collect($existingVotes);

        return collect($categories)
            ->map(function (array $category) use ($votes) {
                $lockedCandidate = $votes->get($category['id']) ?? $votes->get((string) $category['id']);
                $category['locked'] = $lockedCandidate !== null;
                $category['locked_candidate_id'] = $lockedCandidate ? (int) $lockedCandidate : null;

                return $category;
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function buildSharedCategories(Election $election): array
    {
        $election->loadMissing([
            'categories',
            'activeCandidates',
            'activeCandidates.user',
        ]);

        $badgePalette = [
            'bg-violet-500/15 text-violet-200',
            'bg-emerald-500/15 text-emerald-200',
            'bg-amber-500/15 text-amber-200',
            'bg-rose-500/15 text-rose-200',
            'bg-sky-500/15 text-sky-200',
        ];

        return $election->categories->map(function ($category) use ($election, $badgePalette) {
            $candidates = $election->activeCandidates
                ->where('election_category_id', $category->id)
                ->map(function ($candidate) use ($badgePalette) {
                    $grade = $candidate->grade_level ?: $candidate->user?->grade_level;
                    $section = $candidate->section ?: $candidate->user?->section;
                    $party = $candidate->party_or_group ?: 'Independent';

                    return [
                        'id' => $candidate->id,
                        'name' => $candidate->display_name,
                        'party' => $party,
                        'badge' => $party === 'Independent'
                            ? 'bg-slate-700/60 text-slate-300'
                            : $badgePalette[crc32($party) % count($badgePalette)],
                        'platform' => $candidate->platform ? Str::limit($candidate->platform, 120) : null,
                        'grade' => $grade,
                        'section' => $section,
                        'photo_path' => $candidate->photo_path,
                        'photo' => EventImageUrl::uploadedUrl($candidate->photo_path),
                        'profile_url' => route('student.candidates.show', $candidate),
                    ];
                })
                ->values()
                ->all();

            return [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description ?? null,
                'votable' => $candidates !== [],
                'candidates' => $candidates,
            ];
        })->values()->all();
    }
}
