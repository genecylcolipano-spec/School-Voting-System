<?php

namespace App\Services\Election;

use App\Exceptions\VoteIntegrityException;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\User;
use Illuminate\Support\Collection;

class BallotSubmissionService
{
    /**
     * Category IDs that still require a vote for this student.
     *
     * @return list<int>
     */
    public function pendingCategoryIds(Election $election, User $student): array
    {
        $votable = $election->votableCategoryIds();

        if ($votable === []) {
            return [];
        }

        $voted = $student->votes()
            ->where('election_id', $election->id)
            ->whereIn('election_category_id', $votable)
            ->pluck('election_category_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return array_values(array_diff($votable, $voted));
    }

    /**
     * Validate ballot selections and return active candidates to cast.
     *
     * @param  array<int|string, int|string>  $selections
     * @return list<Candidate>
     */
    public function resolveCandidates(Election $election, User $student, array $selections): array
    {
        if (! $student->canVote()) {
            throw new VoteIntegrityException('Only students are allowed to cast votes.');
        }

        $pendingCategoryIds = $this->pendingCategoryIds($election, $student);

        if ($pendingCategoryIds === []) {
            return [];
        }

        if ($selections === []) {
            throw new VoteIntegrityException('You must select a candidate for every position before submitting your ballot.');
        }

        $submittedCategoryIds = array_map('intval', array_keys($selections));
        sort($submittedCategoryIds);
        $expectedCategoryIds = $pendingCategoryIds;
        sort($expectedCategoryIds);

        if ($submittedCategoryIds !== $expectedCategoryIds) {
            throw new VoteIntegrityException('You must select a candidate for every position before submitting your ballot.');
        }

        $candidateIds = array_map('intval', array_values($selections));

        if (count($candidateIds) !== count(array_unique($candidateIds))) {
            throw new VoteIntegrityException('Each position must have exactly one candidate selection.');
        }

        /** @var Collection<int, Candidate> $candidates */
        $candidates = Candidate::query()
            ->whereIn('id', $candidateIds)
            ->where('election_id', $election->id)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        if ($candidates->count() !== count($candidateIds)) {
            throw new VoteIntegrityException('One or more selected candidates are invalid or inactive.');
        }

        $toCast = [];

        foreach ($selections as $categoryId => $candidateId) {
            $candidate = $candidates->get((int) $candidateId);

            if (! $candidate || (int) $candidate->election_category_id !== (int) $categoryId) {
                throw new VoteIntegrityException('A selected candidate does not match its position. Please review your ballot.');
            }

            $toCast[] = $candidate;
        }

        return $toCast;
    }
}
