<?php

namespace App\Services\Talent;

use App\Enums\TalentJudgeScoreStatus;
use App\Enums\TalentVotingMethod;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\TalentJudgeScoreSheet;
use App\Support\EventImageUrl;
use Illuminate\Support\Collection;

class TalentResultsRankingService
{
    /**
     * Rank approved contestants using student votes, judge points, or the hybrid split.
     *
     * @return list<array<string, mixed>>
     */
    public function rankings(TalentEvent $event): array
    {
        $entries = $event->approvedEntries()
            ->withCount('votes')
            ->orderBy('display_name')
            ->get();

        $method = $event->voting_method ?? TalentVotingMethod::StudentOnly;
        $maxPossible = max(1, (int) $event->judgingCriteria()->sum('max_points'));
        $judgeAverages = $this->submittedJudgeAverages($event, $entries);
        $totalVotes = (int) $entries->sum('votes_count');
        $maxVotes = (int) $entries->max('votes_count');

        $judgePct = (int) ($event->judge_percentage ?? 70);
        $studentPct = (int) ($event->student_vote_percentage ?? max(0, 100 - $judgePct));

        $rows = $entries->map(function (TalentEventEntry $entry) use ($event, $method, $maxPossible, $judgeAverages, $totalVotes, $maxVotes, $judgePct, $studentPct) {
            $studentVotes = (int) $entry->votes_count;
            $judgeScore = (float) ($judgeAverages[$entry->id] ?? 0);
            $judgeNorm = $maxPossible > 0 ? ($judgeScore / $maxPossible) * 100 : 0.0;
            $voteNorm = $maxVotes > 0 ? ($studentVotes / $maxVotes) * 100 : 0.0;

            $metric = match ($method) {
                TalentVotingMethod::JudgesOnly => round($judgeScore, 2),
                TalentVotingMethod::JudgesAndStudents => round(
                    (($judgeNorm * $judgePct) + ($voteNorm * $studentPct)) / 100,
                    2,
                ),
                default => (float) $studentVotes,
            };

            return [
                'id' => $entry->id,
                'name' => $entry->display_name,
                'position' => $entry->grade_level
                    ? 'Grade '.$entry->grade_level.($entry->section ? ' · '.$entry->section : '')
                    : 'Contestant',
                'category' => $entry->talentCategoryLabel()
                    ?? $event->talent_category?->label()
                    ?? '—',
                'party' => '—',
                'student_votes' => $studentVotes,
                'judge_score' => round($judgeScore, 2),
                'votes' => $metric,
                'metric' => $metric,
                'photo' => $entry->photoUrl(),
                'photo_url' => EventImageUrl::hasUploadedImage($entry->photo_path)
                    ? EventImageUrl::resolve($entry->photo_path)
                    : $entry->photoUrl(),
            ];
        });

        $ranked = $this->assignCompetitionRanks($rows->all(), 'metric');
        $metricTotal = collect($ranked)->sum('metric');

        return collect($ranked)->map(function (array $row) use ($metricTotal) {
            $metric = (float) $row['metric'];
            $isWinner = (int) $row['rank'] === 1 && $metric > 0;

            return array_merge($row, [
                'percent' => $metricTotal > 0 ? round(($metric / $metricTotal) * 100, 1) : 0.0,
                'status' => $isWinner ? 'Winner' : ($metric > 0 ? 'Finalist' : 'No votes'),
            ]);
        })->all();
    }

    public function metricLabel(TalentEvent $event): string
    {
        return match ($event->voting_method ?? TalentVotingMethod::StudentOnly) {
            TalentVotingMethod::JudgesOnly => 'Judge score',
            TalentVotingMethod::JudgesAndStudents => 'Combined score',
            default => 'Votes',
        };
    }

    /**
     * Rank-1 contestants with a positive metric (ties included).
     *
     * @return list<array{name: string, metric: float, rank: int}>
     */
    public function winners(TalentEvent $event): array
    {
        return collect($this->rankings($event))
            ->filter(fn (array $row) => ($row['status'] ?? '') === 'Winner')
            ->map(fn (array $row) => [
                'name' => (string) ($row['name'] ?? ''),
                'metric' => (float) ($row['metric'] ?? 0),
                'rank' => (int) ($row['rank'] ?? 0),
            ])
            ->values()
            ->all();
    }

    /**
     * Competition ranking: ties share a rank and the next rank is skipped (1, 1, 3).
     *
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    public function assignCompetitionRanks(array $rows, string $scoreKey = 'votes'): array
    {
        $sorted = collect($rows)
            ->sort(function (array $left, array $right) use ($scoreKey) {
                $scoreCmp = (float) ($right[$scoreKey] ?? 0) <=> (float) ($left[$scoreKey] ?? 0);

                if ($scoreCmp !== 0) {
                    return $scoreCmp;
                }

                return strcasecmp((string) ($left['name'] ?? ''), (string) ($right['name'] ?? ''));
            })
            ->values();

        $rank = 0;
        $seen = 0;
        $previous = null;
        $output = [];

        foreach ($sorted as $row) {
            $seen++;
            $score = round((float) ($row[$scoreKey] ?? 0), 4);

            if ($previous === null || $score < $previous) {
                $rank = $seen;
                $previous = $score;
            }

            $output[] = array_merge($row, ['rank' => $rank]);
        }

        return $output;
    }

    /**
     * @param  Collection<int, TalentEventEntry>  $entries
     * @return array<int, float>
     */
    protected function submittedJudgeAverages(TalentEvent $event, Collection $entries): array
    {
        if ($entries->isEmpty() || ! $event->requiresJudges()) {
            return [];
        }

        $averages = TalentJudgeScoreSheet::query()
            ->where('talent_event_id', $event->id)
            ->where('status', TalentJudgeScoreStatus::Submitted)
            ->whereIn('talent_event_entry_id', $entries->modelKeys())
            ->selectRaw('talent_event_entry_id, AVG(total_score) as avg_score')
            ->groupBy('talent_event_entry_id')
            ->pluck('avg_score', 'talent_event_entry_id');

        return $averages->map(fn ($score) => (float) $score)->all();
    }
}
