<?php

namespace Tests\Unit\Talent;

use App\Enums\TalentEventStatus;
use App\Enums\TalentVotingMethod;
use App\Models\Election;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\User;
use App\Services\Talent\StudentTalentHeroActionResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentTalentHeroActionResolverTest extends TestCase
{
    use RefreshDatabase;

    protected StudentTalentHeroActionResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = app(StudentTalentHeroActionResolver::class);
    }

    protected function makeEvent(array $overrides = []): TalentEvent
    {
        $election = Election::factory()->create();

        return TalentEvent::query()->create(array_merge([
            'election_id' => $election->id,
            'title' => 'Hero CTA Showcase',
            'slug' => 'hero-cta-showcase',
            'event_date' => now()->addDays(3),
            'status' => TalentEventStatus::Scheduled,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'registration_starts_at' => now()->subDay(),
            'registration_ends_at' => now()->addDay(),
            'voting_starts_at' => now()->addDays(2),
            'voting_ends_at' => now()->addDays(4),
            'published_to_students' => true,
            'created_by' => User::factory()->admin()->create()->id,
        ], $overrides));
    }

    protected function makeEntry(TalentEvent $event, User $student): TalentEventEntry
    {
        return TalentEventEntry::query()->create([
            'talent_event_id' => $event->id,
            'user_id' => $student->id,
            'display_name' => $student->name,
            'performance_title' => 'My Act',
            'status' => TalentEventEntry::STATUS_PENDING,
            'source' => TalentEventEntry::SOURCE_SELF,
            'submitted_at' => now(),
        ]);
    }

    public function test_registration_open_shows_register_now_for_non_participant(): void
    {
        $event = $this->makeEvent();
        $student = User::factory()->create();

        $actions = $this->resolver->resolve($event, $student, false, null);

        $this->assertSame('registration_open', $actions['phase']);
        $this->assertSame('Register Now', $actions['primary']['label']);
        $this->assertNull($actions['secondary']);
    }

    public function test_registration_open_participant_has_no_view_my_entry_hero_action(): void
    {
        $event = $this->makeEvent();
        $student = User::factory()->create();
        $entry = $this->makeEntry($event, $student);

        $actions = $this->resolver->resolve($event, $student, false, $entry);

        $this->assertNull($actions['primary']);
        $this->assertNull($actions['secondary']);
        $this->assertStringNotContainsString('View My Entry', json_encode($actions));
    }

    public function test_voting_open_shows_vote_now(): void
    {
        $event = $this->makeEvent([
            'status' => TalentEventStatus::VotingOpen,
            'registration_starts_at' => now()->subDays(5),
            'registration_ends_at' => now()->subDay(),
            'voting_starts_at' => now()->subHour(),
            'voting_ends_at' => now()->addDay(),
        ]);
        $student = User::factory()->create();
        $entry = $this->makeEntry($event, $student);

        $actions = $this->resolver->resolve($event, $student, false, $entry);

        $this->assertSame('Vote Now', $actions['primary']['label']);
        $this->assertNull($actions['secondary']);
        $this->assertStringNotContainsString('View My Entry', json_encode($actions));
    }

    public function test_voting_closed_shows_results_pending_only(): void
    {
        $event = $this->makeEvent([
            'status' => TalentEventStatus::VotingOpen,
            'registration_starts_at' => now()->subDays(10),
            'registration_ends_at' => now()->subDays(5),
            'voting_starts_at' => now()->subDays(3),
            'voting_ends_at' => now()->subHour(),
            'results_published_at' => null,
        ]);
        $student = User::factory()->create();
        $entry = $this->makeEntry($event, $student);

        $actions = $this->resolver->resolve($event, $student, true, $entry);

        $this->assertSame('Results Pending', $actions['primary']['label']);
        $this->assertTrue($actions['primary']['disabled']);
        $this->assertNull($actions['secondary']);
        $this->assertStringNotContainsString('View My Entry', json_encode($actions));
    }

    public function test_results_published_shows_view_results_only(): void
    {
        $event = $this->makeEvent([
            'status' => TalentEventStatus::ResultsPublished,
            'registration_starts_at' => now()->subDays(10),
            'registration_ends_at' => now()->subDays(8),
            'voting_starts_at' => now()->subDays(5),
            'voting_ends_at' => now()->subDay(),
            'results_published_at' => now()->subHour(),
        ]);
        $student = User::factory()->create();
        $entry = $this->makeEntry($event, $student);

        $actions = $this->resolver->resolve($event, $student, true, $entry);

        $this->assertSame('View Results', $actions['primary']['label']);
        $this->assertNull($actions['secondary']);
        $this->assertStringNotContainsString('View My Entry', json_encode($actions));
    }

    public function test_registration_closed_shows_registration_closed(): void
    {
        $event = $this->makeEvent([
            'registration_starts_at' => now()->subDays(5),
            'registration_ends_at' => now()->subDay(),
            'voting_starts_at' => now()->addDay(),
            'voting_ends_at' => now()->addDays(2),
        ]);
        $student = User::factory()->create();

        $actions = $this->resolver->resolve($event, $student, false, null);

        $this->assertSame('Registration Closed', $actions['primary']['label']);
        $this->assertTrue($actions['primary']['disabled']);
    }
}
