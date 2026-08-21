<?php

namespace Tests\Unit\Talent;

use App\Enums\TalentEventStatus;
use App\Enums\TalentJudgeScoreStatus;
use App\Enums\TalentVotingMethod;
use App\Models\Election;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\TalentEventVote;
use App\Models\TalentJudgeScoreSheet;
use App\Models\TalentJudgingCriterion;
use App\Models\User;
use App\Services\Talent\TalentResultsRankingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TalentResultsRankingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_only_ranks_by_votes(): void
    {
        $event = $this->makeCompetition(['voting_method' => TalentVotingMethod::StudentOnly->value]);
        $leader = $this->makeEntry($event, 'Vote Leader');
        $runner = $this->makeEntry($event, 'Vote Runner');

        $this->castVotes($event, $leader, 3);
        $this->castVotes($event, $runner, 1);

        $rankings = app(TalentResultsRankingService::class)->rankings($event);

        $this->assertSame('Vote Leader', $rankings[0]['name']);
        $this->assertSame(1, $rankings[0]['rank']);
        $this->assertSame('Winner', $rankings[0]['status']);
        $this->assertSame(3.0, (float) $rankings[0]['votes']);
        $this->assertSame('Vote Runner', $rankings[1]['name']);
        $this->assertSame(2, $rankings[1]['rank']);
    }

    public function test_judges_only_ranks_by_average_submitted_score(): void
    {
        $event = $this->makeCompetition(['voting_method' => TalentVotingMethod::JudgesOnly->value]);
        $this->addCriterion($event, 100);
        $high = $this->makeEntry($event, 'High Score');
        $low = $this->makeEntry($event, 'Low Score');

        $this->castVotes($event, $low, 5);
        $this->submitScore($event, $high, 90);
        $this->submitScore($event, $low, 40);

        $rankings = app(TalentResultsRankingService::class)->rankings($event);

        $this->assertSame('High Score', $rankings[0]['name']);
        $this->assertSame(1, $rankings[0]['rank']);
        $this->assertEquals(90.0, $rankings[0]['judge_score']);
        $this->assertSame('Winner', $rankings[0]['status']);
        $this->assertSame('Low Score', $rankings[1]['name']);
    }

    public function test_hybrid_uses_judge_and_student_percentages(): void
    {
        $event = $this->makeCompetition([
            'voting_method' => TalentVotingMethod::JudgesAndStudents->value,
            'judge_percentage' => 70,
            'student_vote_percentage' => 30,
        ]);
        $this->addCriterion($event, 100);
        $judgeFavorite = $this->makeEntry($event, 'Judge Favorite');
        $crowdFavorite = $this->makeEntry($event, 'Crowd Favorite');

        $this->castVotes($event, $judgeFavorite, 10);
        $this->castVotes($event, $crowdFavorite, 20);
        $this->submitScore($event, $judgeFavorite, 90);
        $this->submitScore($event, $crowdFavorite, 50);

        $rankings = app(TalentResultsRankingService::class)->rankings($event);

        $this->assertSame('Judge Favorite', $rankings[0]['name']);
        $this->assertEquals(78.0, $rankings[0]['metric']);
        $this->assertSame('Crowd Favorite', $rankings[1]['name']);
        $this->assertEquals(65.0, $rankings[1]['metric']);
    }

    public function test_tied_top_scores_share_first_and_are_both_winners(): void
    {
        $event = $this->makeCompetition(['voting_method' => TalentVotingMethod::StudentOnly->value]);
        $alpha = $this->makeEntry($event, 'Alpha');
        $bravo = $this->makeEntry($event, 'Bravo');
        $charlie = $this->makeEntry($event, 'Charlie');

        $this->castVotes($event, $alpha, 4);
        $this->castVotes($event, $bravo, 4);
        $this->castVotes($event, $charlie, 1);

        $rankings = app(TalentResultsRankingService::class)->rankings($event);

        $this->assertSame(1, $rankings[0]['rank']);
        $this->assertSame(1, $rankings[1]['rank']);
        $this->assertSame('Winner', $rankings[0]['status']);
        $this->assertSame('Winner', $rankings[1]['status']);
        $this->assertSame(3, $rankings[2]['rank']);
        $this->assertSame('Charlie', $rankings[2]['name']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeCompetition(array $overrides = []): TalentEvent
    {
        $election = Election::factory()->create();

        return TalentEvent::query()->create(array_merge([
            'election_id' => $election->id,
            'title' => 'Results Ranking Night',
            'slug' => 'results-ranking-'.uniqid(),
            'event_date' => now()->subDay(),
            'venue' => 'Online',
            'status' => TalentEventStatus::VotingOpen,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'voting_starts_at' => now()->subHour(),
            'voting_ends_at' => now()->addHour(),
            'published_to_students' => true,
            'created_by' => User::factory()->admin()->create()->id,
        ], $overrides));
    }

    protected function makeEntry(TalentEvent $event, string $name): TalentEventEntry
    {
        return TalentEventEntry::query()->create([
            'talent_event_id' => $event->id,
            'display_name' => $name,
            'performance_title' => $name.' Act',
            'status' => TalentEventEntry::STATUS_APPROVED,
            'source' => TalentEventEntry::SOURCE_ADMIN,
        ]);
    }

    protected function addCriterion(TalentEvent $event, int $maxPoints): TalentJudgingCriterion
    {
        return TalentJudgingCriterion::query()->create([
            'talent_event_id' => $event->id,
            'name' => 'Overall',
            'max_points' => $maxPoints,
            'sort_order' => 1,
        ]);
    }

    protected function submitScore(TalentEvent $event, TalentEventEntry $entry, float $score): void
    {
        TalentJudgeScoreSheet::query()->create([
            'talent_event_id' => $event->id,
            'user_id' => User::factory()->faculty()->create()->id,
            'talent_event_entry_id' => $entry->id,
            'total_score' => $score,
            'status' => TalentJudgeScoreStatus::Submitted,
            'submitted_at' => now(),
        ]);
    }

    protected function castVotes(TalentEvent $event, TalentEventEntry $entry, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            TalentEventVote::query()->create([
                'talent_event_id' => $event->id,
                'talent_event_entry_id' => $entry->id,
                'user_id' => User::factory()->create()->id,
                'voted_at' => now(),
            ]);
        }
    }
}
