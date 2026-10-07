<?php

namespace Tests\Feature\Talent;

use App\Enums\TalentEventStatus;
use App\Enums\TalentVotingMethod;
use App\Models\Election;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TalentCompetitionDeleteLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_draft_competition_can_be_deleted(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $event = $this->makeDraftCompetition(['created_by' => $admin->id]);

        $this->assertTrue($event->canBeDeleted());

        $this->actingAs($admin)
            ->from(route('admin.talent-competition.index'))
            ->delete(route('admin.talent-competition.destroy', $event))
            ->assertRedirect(route('admin.talent-competition.index'))
            ->assertSessionHas('success');

        $this->assertTrue($event->fresh()->trashed());
    }

    public function test_registration_open_with_participants_can_be_deleted(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $event = $this->makeRegistrationCompetition(['created_by' => $admin->id]);
        $this->makeEntry($event, 'Ana');

        $this->assertTrue($event->fresh()->canBeDeleted());

        $this->actingAs($admin)
            ->delete(route('admin.talent-competition.destroy', $event))
            ->assertRedirect(route('admin.talent-competition.index'))
            ->assertSessionHas('success');

        $this->assertTrue($event->fresh()->trashed());
    }

    public function test_voting_open_cannot_be_deleted(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $event = $this->makeOpenCompetition(['created_by' => $admin->id]);

        $this->assertFalse($event->canBeDeleted());
        $this->assertSame(
            'This competition cannot be deleted after voting has started or results are published. Archive it instead.',
            $event->deletionBlockReason(),
        );

        $this->actingAs($admin)
            ->from(route('admin.talent-competition.index'))
            ->delete(route('admin.talent-competition.destroy', $event))
            ->assertRedirect(route('admin.talent-competition.index'))
            ->assertSessionHas('error', $event->deletionBlockReason());

        $this->assertFalse($event->fresh()->trashed());
    }

    public function test_voting_paused_or_closed_cannot_be_deleted(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $paused = $this->makeOpenCompetition([
            'created_by' => $admin->id,
            'title' => 'Paused Showcase',
            'is_paused' => true,
        ]);
        $closed = $this->makeOpenCompetition([
            'created_by' => $admin->id,
            'title' => 'Closed Showcase',
            'voting_starts_at' => now()->subDays(2),
            'voting_ends_at' => now()->subHour(),
        ]);

        $this->assertFalse($paused->canBeDeleted());
        $this->assertFalse($closed->canBeDeleted());

        $this->actingAs($admin)
            ->from(route('admin.talent-competition.index'))
            ->delete(route('admin.talent-competition.destroy', $paused))
            ->assertRedirect(route('admin.talent-competition.index'))
            ->assertSessionHas('error');

        $this->actingAs($admin)
            ->from(route('admin.talent-competition.index'))
            ->delete(route('admin.talent-competition.destroy', $closed))
            ->assertRedirect(route('admin.talent-competition.index'))
            ->assertSessionHas('error');

        $this->assertFalse($paused->fresh()->trashed());
        $this->assertFalse($closed->fresh()->trashed());
    }

    public function test_results_published_cannot_be_deleted(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $event = $this->makeOpenCompetition([
            'created_by' => $admin->id,
            'status' => TalentEventStatus::ResultsPublished,
            'results_published_at' => now(),
            'voting_starts_at' => now()->subDays(2),
            'voting_ends_at' => now()->subHour(),
        ]);

        $this->assertFalse($event->canBeDeleted());

        $this->actingAs($admin)
            ->from(route('admin.talent-competition.index'))
            ->delete(route('admin.talent-competition.destroy', $event))
            ->assertRedirect(route('admin.talent-competition.index'))
            ->assertSessionHas('error');

        $this->assertFalse($event->fresh()->trashed());
    }

    public function test_archived_cannot_be_deleted(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $event = $this->makeOpenCompetition([
            'created_by' => $admin->id,
            'status' => TalentEventStatus::Completed,
            'voting_starts_at' => now()->subDays(2),
            'voting_ends_at' => now()->subHour(),
        ]);

        $this->assertFalse($event->canBeDeleted());

        $this->actingAs($admin)
            ->from(route('admin.talent-competition.index'))
            ->delete(route('admin.talent-competition.destroy', $event))
            ->assertRedirect(route('admin.talent-competition.index'))
            ->assertSessionHas('error');

        $this->assertFalse($event->fresh()->trashed());
    }

    public function test_index_hides_delete_after_results_published_and_shows_it_for_drafts(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $draft = $this->makeDraftCompetition([
            'created_by' => $admin->id,
            'title' => 'Draft Showcase',
        ]);
        $published = $this->makeOpenCompetition([
            'created_by' => $admin->id,
            'title' => 'Rosemond Idols',
            'status' => TalentEventStatus::ResultsPublished,
            'results_published_at' => now(),
            'voting_starts_at' => now()->subDays(2),
            'voting_ends_at' => now()->subHour(),
        ]);

        $html = $this->actingAs($admin)
            ->get(route('admin.talent-competition.index'))
            ->assertOk()
            ->assertSee('Draft Showcase')
            ->assertSee('Rosemond Idols')
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/action="'.preg_quote(route('admin.talent-competition.destroy', $draft), '/').'"[\s\S]{0,500}name="_method" value="DELETE"/',
            $html,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/action="'.preg_quote(route('admin.talent-competition.destroy', $published), '/').'"[\s\S]{0,500}name="_method" value="DELETE"/',
            $html,
        );
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeDraftCompetition(array $overrides = []): TalentEvent
    {
        return $this->makeCompetition(array_merge([
            'title' => 'Draft Showcase',
            'status' => TalentEventStatus::Scheduled,
            'published_to_students' => false,
            'registration_starts_at' => now()->addDay(),
            'registration_ends_at' => now()->addDays(5),
            'voting_starts_at' => now()->addDays(6),
            'voting_ends_at' => now()->addDays(8),
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeRegistrationCompetition(array $overrides = []): TalentEvent
    {
        return $this->makeCompetition(array_merge([
            'title' => 'Registration Showcase',
            'status' => TalentEventStatus::EntriesOpen,
            'published_to_students' => true,
            'registration_starts_at' => now()->subDay(),
            'registration_ends_at' => now()->addDay(),
            'voting_starts_at' => now()->addDays(2),
            'voting_ends_at' => now()->addDays(4),
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeOpenCompetition(array $overrides = []): TalentEvent
    {
        return $this->makeCompetition(array_merge([
            'title' => 'Live Showcase',
            'status' => TalentEventStatus::VotingOpen,
            'published_to_students' => true,
            'registration_starts_at' => now()->subDays(5),
            'registration_ends_at' => now()->subDay(),
            'voting_starts_at' => now()->subHour(),
            'voting_ends_at' => now()->addDay(),
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeCompetition(array $overrides = []): TalentEvent
    {
        $election = Election::factory()->create();

        return TalentEvent::query()->create(array_merge([
            'election_id' => $election->id,
            'title' => 'Showcase',
            'slug' => 'showcase-'.uniqid(),
            'event_date' => now()->addDay(),
            'venue' => 'Auditorium',
            'status' => TalentEventStatus::Scheduled,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'published_to_students' => false,
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
}
