<?php

namespace Tests\Unit\Talent;

use App\Enums\TalentEventStatus;
use App\Enums\TalentVotingMethod;
use App\Models\TalentEvent;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TalentEventJudgingPhaseTest extends TestCase
{
    public function test_judging_is_scheduled_before_the_voting_window(): void
    {
        $at = Carbon::parse('2026-08-24 10:00:00');
        $event = $this->makeEvent([
            'voting_starts_at' => Carbon::parse('2026-08-25 18:00:00'),
            'voting_ends_at' => Carbon::parse('2026-08-26 18:00:00'),
            'status' => TalentEventStatus::Scheduled,
        ]);

        $this->assertSame('scheduled', $event->judgingPhaseKey($at));
        $this->assertSame('Judging scheduled', $event->judgingPhaseLabel($at));
        $this->assertSame('Aug 25, 2026 6:00 PM', $event->judgingPhase($at)['opens_at']);
        $this->assertFalse($event->isAcceptingJudgeScores($at));
    }

    public function test_judging_is_open_inside_the_voting_window(): void
    {
        $at = Carbon::parse('2026-08-25 19:00:00');
        $event = $this->makeEvent([
            'voting_starts_at' => Carbon::parse('2026-08-25 18:00:00'),
            'voting_ends_at' => Carbon::parse('2026-08-26 18:00:00'),
            'status' => TalentEventStatus::VotingOpen,
        ]);

        $this->assertSame('open', $event->judgingPhaseKey($at));
        $this->assertSame('Judging open', $event->judgingPhaseLabel($at));
        $this->assertSame('Aug 26, 2026 6:00 PM', $event->judgingPhase($at)['closes_at']);
        $this->assertTrue($event->isAcceptingJudgeScores($at));
    }

    public function test_judging_is_closed_after_the_voting_window(): void
    {
        $at = Carbon::parse('2026-08-26 19:00:00');
        $event = $this->makeEvent([
            'voting_starts_at' => Carbon::parse('2026-08-25 18:00:00'),
            'voting_ends_at' => Carbon::parse('2026-08-26 18:00:00'),
            'status' => TalentEventStatus::VotingOpen,
        ]);

        $this->assertSame('closed', $event->judgingPhaseKey($at));
        $this->assertSame('Judging closed', $event->judgingPhaseLabel($at));
        $this->assertFalse($event->isAcceptingJudgeScores($at));
    }

    public function test_paused_or_completed_competitions_are_closed_not_scheduled(): void
    {
        $at = Carbon::parse('2026-08-24 10:00:00');
        $starts = Carbon::parse('2026-08-25 18:00:00');

        $paused = $this->makeEvent([
            'voting_starts_at' => $starts,
            'voting_ends_at' => Carbon::parse('2026-08-26 18:00:00'),
            'status' => TalentEventStatus::Scheduled,
            'is_paused' => true,
        ]);
        $completed = $this->makeEvent([
            'voting_starts_at' => $starts,
            'voting_ends_at' => Carbon::parse('2026-08-26 18:00:00'),
            'status' => TalentEventStatus::Completed,
        ]);

        $this->assertSame('closed', $paused->judgingPhaseKey($at));
        $this->assertSame('closed', $completed->judgingPhaseKey($at));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function makeEvent(array $attributes): TalentEvent
    {
        $event = new TalentEvent;
        $event->forceFill(array_merge([
            'voting_method' => TalentVotingMethod::JudgesOnly,
            'is_paused' => false,
            'results_published_at' => null,
        ], $attributes));

        return $event;
    }
}
