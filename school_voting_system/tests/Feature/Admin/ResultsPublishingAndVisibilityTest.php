<?php

namespace Tests\Feature\Admin;

use App\Enums\ElectionStatus;
use App\Enums\TalentEventStatus;
use App\Enums\TalentVotingMethod;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionCategory;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\User;
use App\Models\Vote;
use App\Services\Admin\AdminResultsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ResultsPublishingAndVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_talent_results_cannot_be_published_while_judging_is_open(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $event = $this->makeCompetition([
            'voting_method' => TalentVotingMethod::JudgesOnly->value,
            'voting_starts_at' => now()->subHour(),
            'voting_ends_at' => now()->addDay(),
            'status' => TalentEventStatus::VotingOpen,
        ]);
        $this->makeEntry($event);

        $this->actingAs($admin)
            ->from(route('admin.results.talent.show', $event))
            ->post(route('admin.talent.publish-results', $event))
            ->assertStatus(422);

        $this->assertNull($event->fresh()->results_published_at);
    }

    public function test_talent_results_can_be_published_after_voting_ends_and_unpublished(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $student = User::factory()->create();
        $faculty = User::factory()->faculty()->create();
        $event = $this->makeCompetition([
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'voting_starts_at' => now()->subDays(2),
            'voting_ends_at' => now()->subHour(),
            'status' => TalentEventStatus::VotingOpen,
        ]);
        $this->makeEntry($event);

        Queue::fake();

        $this->actingAs($admin)
            ->post(route('admin.talent.publish-results', $event))
            ->assertRedirect(route('admin.results.talent.show', $event));

        $event->refresh();
        $this->assertNotNull($event->results_published_at);
        $this->assertSame(TalentEventStatus::ResultsPublished, $event->status);
        $this->assertDatabaseHas('portal_notifications', [
            'user_id' => $student->id,
            'type' => 'student_talent_results_published',
            'related_id' => $event->id,
        ]);
        $this->assertDatabaseHas('portal_notifications', [
            'user_id' => $faculty->id,
            'type' => 'faculty_talent_results_published',
            'related_id' => $event->id,
        ]);

        $this->actingAs($faculty)
            ->getJson(route('faculty.notifications.feed'))
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonFragment([
                'type' => 'faculty_talent_results_published',
                'url' => route('faculty.results.talent.show', $event),
            ]);

        $this->actingAs($admin)
            ->post(route('admin.talent.unpublish-results', $event))
            ->assertRedirect(route('admin.results.talent.show', $event));

        $event->refresh();
        $this->assertNull($event->results_published_at);
        $this->assertSame(TalentEventStatus::VotingOpen, $event->status);
    }

    public function test_published_election_results_notify_students_and_faculty(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $student = User::factory()->create();
        $faculty = User::factory()->faculty()->create();
        $election = Election::factory()->closed()->create([
            'title' => 'SSC Election Results',
            'public_results_published' => false,
        ]);

        Queue::fake();

        $this->actingAs($admin)
            ->post(route('admin.election.publish-results', $election))
            ->assertRedirect(route('admin.results.election.show', $election));

        $this->assertTrue($election->fresh()->public_results_published);
        $this->assertDatabaseHas('portal_notifications', [
            'user_id' => $student->id,
            'type' => 'student_results_published',
            'related_id' => $election->id,
        ]);
        $this->assertDatabaseHas('portal_notifications', [
            'user_id' => $faculty->id,
            'type' => 'faculty_results_published',
            'related_id' => $election->id,
        ]);

        $this->actingAs($faculty)
            ->getJson(route('faculty.notifications.feed'))
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonFragment([
                'type' => 'faculty_results_published',
                'url' => route('faculty.results.election.show', $election),
            ]);

        $this->assertSame(
            1,
            \App\Models\Announcement::query()
                ->where('auto_source_type', 'results_published')
                ->where('auto_source_id', $election->id)
                ->count()
        );

        $this->actingAs($admin)
            ->post(route('admin.election.unpublish-results', $election))
            ->assertRedirect(route('admin.results.election.show', $election));

        $this->assertFalse(
            \App\Models\Announcement::query()
                ->published()
                ->where('auto_source_type', 'results_published')
                ->where('auto_source_id', $election->id)
                ->exists()
        );

        $this->actingAs($admin)
            ->post(route('admin.election.publish-results', $election))
            ->assertRedirect(route('admin.results.election.show', $election));

        $this->assertSame(
            1,
            \App\Models\Announcement::query()
                ->where('auto_source_type', 'results_published')
                ->where('auto_source_id', $election->id)
                ->count()
        );
    }

    public function test_students_cannot_see_draft_or_archived_unpublished_elections_on_results(): void
    {
        $student = User::factory()->create();
        $draft = Election::factory()->draft()->create(['title' => 'Hidden Draft']);
        $archived = Election::factory()->create([
            'title' => 'Hidden Archive',
            'status' => ElectionStatus::Archived,
            'public_results_published' => false,
        ]);
        $closed = Election::factory()->closed()->create(['title' => 'Visible Closed']);

        $this->actingAs($student)
            ->get(route('student.results.index'))
            ->assertOk()
            ->assertSee('Visible Closed')
            ->assertDontSee('Hidden Draft')
            ->assertDontSee('Hidden Archive');

        $this->actingAs($student)
            ->get(route('student.results.election.show', $draft))
            ->assertNotFound();

        $this->actingAs($student)
            ->get(route('student.results.election.show', $archived))
            ->assertNotFound();

        $this->actingAs($student)
            ->get(route('student.results.election.show', $closed))
            ->assertOk()
            ->assertSee('Welcome back')
            ->assertSee('Under Review')
            ->assertSee('Results are not yet available.')
            ->assertDontSee('Voting is still ongoing.');
    }

    public function test_tied_election_candidates_are_both_winners(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $election = Election::factory()->active()->create();
        $category = ElectionCategory::factory()->create([
            'election_id' => $election->id,
            'name' => 'President',
            'sort_order' => 1,
        ]);
        $alpha = Candidate::factory()->create([
            'election_id' => $election->id,
            'election_category_id' => $category->id,
            'display_name' => 'Alex Alpha',
        ]);
        $bravo = Candidate::factory()->create([
            'election_id' => $election->id,
            'election_category_id' => $category->id,
            'display_name' => 'Blair Bravo',
        ]);

        Vote::query()->create([
            'user_id' => User::factory()->create()->id,
            'election_id' => $election->id,
            'election_category_id' => $category->id,
            'candidate_id' => $alpha->id,
            'voted_at' => now(),
        ]);
        Vote::query()->create([
            'user_id' => User::factory()->create()->id,
            'election_id' => $election->id,
            'election_category_id' => $category->id,
            'candidate_id' => $bravo->id,
            'voted_at' => now(),
        ]);

        $detail = app(AdminResultsService::class)->electionDetail($election, $admin);
        $winners = collect($detail['rankings'])->where('status', 'Winner')->values();

        $this->assertCount(2, $winners);
        $this->assertTrue($winners->every(fn (array $row) => (int) $row['rank'] === 1));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeCompetition(array $overrides = []): TalentEvent
    {
        $election = Election::factory()->create();

        return TalentEvent::query()->create(array_merge([
            'election_id' => $election->id,
            'title' => 'Publish Gate Showcase',
            'slug' => 'publish-gate-'.uniqid(),
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

    protected function makeEntry(TalentEvent $event): TalentEventEntry
    {
        return TalentEventEntry::query()->create([
            'talent_event_id' => $event->id,
            'display_name' => 'Contestant One',
            'performance_title' => 'Solo',
            'status' => TalentEventEntry::STATUS_APPROVED,
            'source' => TalentEventEntry::SOURCE_ADMIN,
        ]);
    }
}
