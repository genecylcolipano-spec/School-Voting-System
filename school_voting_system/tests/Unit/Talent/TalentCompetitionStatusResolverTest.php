<?php

namespace Tests\Unit\Talent;

use App\Enums\TalentEventStatus;
use App\Enums\TalentRegistrationMethod;
use App\Enums\TalentVotingMethod;
use App\Models\TalentEvent;
use App\Services\Talent\TalentCompetitionStatusResolver;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TalentCompetitionStatusResolverTest extends TestCase
{
    protected TalentCompetitionStatusResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new TalentCompetitionStatusResolver;
    }

    public function test_registration_open_before_voting_starts(): void
    {
        $at = Carbon::parse('2026-07-25 21:30:00');
        $event = $this->makeEvent([
            'published_to_students' => true,
            'registration_starts_at' => Carbon::parse('2026-07-25 21:18:00'),
            'registration_ends_at' => Carbon::parse('2026-07-25 22:18:00'),
            'voting_starts_at' => Carbon::parse('2026-07-26 21:18:00'),
            'voting_ends_at' => Carbon::parse('2026-07-27 21:18:00'),
            'status' => TalentEventStatus::Scheduled,
        ]);

        $this->assertSame('registration_open', $this->resolver->key($event, $at));
        $this->assertFalse($event->isAcceptingVotes($at));
        $this->assertTrue($event->isRegistrationOpen($at));
    }

    public function test_voting_end_alone_does_not_open_voting(): void
    {
        $at = Carbon::parse('2026-07-25 21:30:00');
        $event = $this->makeEvent([
            'published_to_students' => true,
            'registration_starts_at' => Carbon::parse('2026-07-25 21:18:00'),
            'registration_ends_at' => Carbon::parse('2026-07-25 22:18:00'),
            'voting_starts_at' => null,
            'voting_ends_at' => Carbon::parse('2026-07-27 21:18:00'),
            'status' => TalentEventStatus::Scheduled,
        ]);

        $this->assertFalse($event->isAcceptingVotes($at));
        $this->assertSame('registration_open', $this->resolver->key($event, $at));
    }

    public function test_voting_open_after_voting_starts(): void
    {
        $at = Carbon::parse('2026-07-26 22:00:00');
        $event = $this->makeEvent([
            'published_to_students' => true,
            'registration_starts_at' => Carbon::parse('2026-07-25 21:18:00'),
            'registration_ends_at' => Carbon::parse('2026-07-25 22:18:00'),
            'voting_starts_at' => Carbon::parse('2026-07-26 21:18:00'),
            'voting_ends_at' => Carbon::parse('2026-07-27 21:18:00'),
            'status' => TalentEventStatus::Scheduled,
        ]);

        $this->assertTrue($event->isAcceptingVotes($at));
        $this->assertSame('voting_open', $this->resolver->key($event, $at));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function makeEvent(array $attributes): TalentEvent
    {
        $event = new TalentEvent;
        $event->forceFill([
            'voting_method' => TalentVotingMethod::StudentOnly,
            'registration_method' => TalentRegistrationMethod::Both,
            'is_paused' => false,
            'results_published_at' => null,
            ...$attributes,
        ]);

        return $event;
    }
}
