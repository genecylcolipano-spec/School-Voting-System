<?php

namespace App\Support;

use Illuminate\Support\Collection;

class WinnerSpotlightBuilder
{
    /**
     * @param  array<int, array<string, mixed>>  $rankings
     * @return array<int, array<string, mixed>>
     */
    public static function fromRankings(array $rankings): array
    {
        $grouped = collect($rankings)->groupBy('position');

        return $grouped->map(function (Collection $candidates, string $position) {
            $sorted = $candidates->sortBy('rank')->values();
            $winner = $sorted->first();

            if (! $winner || ($winner['rank'] ?? 0) !== 1 || ($winner['votes'] ?? 0) <= 0) {
                return null;
            }

            $runnerUp = $sorted->first(fn (array $row) => ($row['rank'] ?? 0) === 2);
            $winnerVotes = (int) ($winner['votes'] ?? 0);
            $runnerVotes = (int) ($runnerUp['votes'] ?? 0);
            $winnerPercent = (float) ($winner['percent'] ?? 0);
            $runnerPercent = (float) ($runnerUp['percent'] ?? 0);

            return [
                'label' => $position,
                'position' => $position,
                'name' => $winner['name'] ?? '—',
                'party' => $winner['party'] ?? null,
                'photo_url' => $winner['photo_url'] ?? null,
                'votes' => $winnerVotes,
                'percent' => $winnerPercent,
                'margin_votes' => max(0, $winnerVotes - $runnerVotes),
                'margin_percent' => round(max(0, $winnerPercent - $runnerPercent), 1),
                'candidate_id' => $winner['id'] ?? null,
            ];
        })->filter()->values()->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $spotlight
     * @return array<string, mixed>|null
     */
    public static function primaryWinner(array $spotlight): ?array
    {
        if ($spotlight === []) {
            return null;
        }

        return collect($spotlight)->sortBy(fn (array $row) => self::positionRank($row['position'] ?? $row['label'] ?? ''))->first();
    }

    protected static function positionRank(string $position): int
    {
        $normalized = strtolower(trim($position));

        if (preg_match('/\bvice[\s-]*president\b/', $normalized)) {
            return 2;
        }

        if (preg_match('/\bpresident\b/', $normalized)) {
            return 1;
        }

        if (str_contains($normalized, 'secretary')) {
            return 3;
        }

        if (str_contains($normalized, 'treasurer')) {
            return 4;
        }

        return 100;
    }
}
