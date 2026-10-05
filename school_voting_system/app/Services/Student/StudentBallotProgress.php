<?php

namespace App\Services\Student;

use App\Models\Election;
use App\Models\ElectionCategory;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Support\Collection;

class StudentBallotProgress
{
    /**
     * @param  Collection<int, Election>  $elections
     * @return Collection<int, Election>
     */
    public function remainingElections(Collection $elections, User $student): Collection
    {
        $completed = $this->completedElectionIds($elections, $student);

        return $elections
            ->reject(fn (Election $election) => in_array($election->id, $completed, true))
            ->values();
    }

    /**
     * @param  Collection<int, Election>  $elections
     * @return list<int>
     */
    public function completedElectionIds(Collection $elections, User $student): array
    {
        if ($elections->isEmpty()) {
            return [];
        }

        $electionIds = $elections->pluck('id');

        $categoryIdsByElection = ElectionCategory::query()
            ->whereIn('election_id', $electionIds)
            ->whereHas('candidates', fn ($query) => $query->where('is_active', true))
            ->get(['id', 'election_id', 'audience_course'])
            ->groupBy('election_id')
            ->map(function (Collection $rows) use ($student) {
                return $rows
                    ->filter(fn (ElectionCategory $category) => $category->isVisibleToVoter($student))
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->values();
            });

        $votedCategoryIdsByElection = Vote::query()
            ->where('user_id', $student->id)
            ->whereIn('election_id', $electionIds)
            ->get(['election_id', 'election_category_id'])
            ->groupBy('election_id')
            ->map(fn (Collection $rows) => $rows->pluck('election_category_id')->map(fn ($id) => (int) $id)->unique()->values());

        $completed = [];

        foreach ($elections as $election) {
            $categoryIds = $categoryIdsByElection->get($election->id, collect());
            $voted = $votedCategoryIdsByElection->get($election->id, collect());

            if ($categoryIds->isEmpty()) {
                if ($voted->isNotEmpty()) {
                    $completed[] = (int) $election->id;
                }

                continue;
            }

            if ($voted->intersect($categoryIds)->count() >= $categoryIds->count()) {
                $completed[] = (int) $election->id;
            }
        }

        return $completed;
    }
}
