<?php

namespace Tests\Feature\Admin;

use App\Enums\ElectionStatus;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionCategory;
use App\Models\User;
use App\Models\Vote;
use App\Services\Admin\ElectionSetupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\Concerns\CreatesElectionBallotFixtures;
use Tests\TestCase;

class ElectionSetupLifecycleTest extends TestCase
{
    use CreatesElectionBallotFixtures;
    use RefreshDatabase;

    public function test_creating_an_active_election_opens_voting(): void
    {
        $super = User::factory()->superAdmin()->create();
        $student = User::factory()->create();
        $starts = now()->subHour()->startOfMinute();
        $ends = now()->addDay()->startOfMinute();

        $this->actingAs($super)
            ->post(route('admin.elections.store'), [
                'title' => 'Campus Election',
                'status' => ElectionStatus::Active->value,
                'voting_starts_at' => $starts->format('Y-m-d\TH:i'),
                'voting_ends_at' => $ends->format('Y-m-d\TH:i'),
                'positions' => [['name' => 'President']],
            ])
            ->assertRedirect();

        $election = Election::query()->where('title', 'Campus Election')->first();

        $this->assertNotNull($election);
        $this->assertSame(ElectionStatus::Active, $election->status);
        $this->assertNull($election->scheduled_open_at);
        $this->assertTrue($election->scheduled_close_at->equalTo($ends));
        $this->assertDatabaseHas('portal_notifications', [
            'user_id' => $student->id,
            'type' => 'student_voting_open',
            'related_id' => $election->id,
        ]);
    }

    public function test_creating_a_draft_election_schedules_open_from_voting_start(): void
    {
        $super = User::factory()->superAdmin()->create();
        $student = User::factory()->create();
        $starts = now()->addDay()->startOfMinute();
        $ends = now()->addDays(2)->startOfMinute();

        $this->actingAs($super)
            ->post(route('admin.elections.store'), [
                'title' => 'Upcoming Draft Election',
                'status' => ElectionStatus::Draft->value,
                'voting_starts_at' => $starts->format('Y-m-d\TH:i'),
                'voting_ends_at' => $ends->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect();

        $election = Election::query()->where('title', 'Upcoming Draft Election')->first();

        $this->assertNotNull($election);
        $this->assertSame(ElectionStatus::Draft, $election->status);
        $this->assertTrue($election->scheduled_open_at->equalTo($starts));
        $this->assertTrue($election->scheduled_close_at->equalTo($ends));
        $this->assertDatabaseMissing('portal_notifications', [
            'user_id' => $student->id,
            'type' => 'student_voting_open',
            'related_id' => $election->id,
        ]);
    }

    public function test_updating_election_keeps_slug_when_title_is_unchanged(): void
    {
        $super = User::factory()->superAdmin()->create();
        $election = Election::factory()->draft()->create([
            'title' => 'Sports Fest Election',
            'slug' => 'sports-fest-election',
            'created_by' => $super->id,
        ]);

        $this->actingAs($super)
            ->put(route('admin.elections.update', $election), [
                'title' => 'Sports Fest Election',
                'description' => 'Updated details',
                'status' => ElectionStatus::Draft->value,
                'voting_starts_at' => now()->addDay()->format('Y-m-d\TH:i'),
                'voting_ends_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect();

        $this->assertSame('sports-fest-election', $election->fresh()->slug);
    }

    public function test_existing_positions_can_be_renamed_and_removed(): void
    {
        $election = Election::factory()->draft()->create();
        $keep = ElectionCategory::factory()->create([
            'election_id' => $election->id,
            'name' => 'President',
        ]);
        $remove = ElectionCategory::factory()->create([
            'election_id' => $election->id,
            'name' => 'Treasurer',
        ]);
        Candidate::factory()->create([
            'election_id' => $election->id,
            'election_category_id' => $remove->id,
        ]);

        app(ElectionSetupService::class)->syncOnUpdate($election, [
            'existing_positions' => [
                $keep->id => ['name' => 'Vice President'],
                $remove->id => ['name' => 'Treasurer', 'remove' => '1'],
            ],
        ]);

        $this->assertDatabaseHas('election_categories', [
            'id' => $keep->id,
            'name' => 'Vice President',
        ]);
        $this->assertDatabaseMissing('election_categories', ['id' => $remove->id]);
        $this->assertDatabaseMissing('candidates', ['election_category_id' => $remove->id]);
    }

    public function test_positions_with_votes_cannot_be_removed(): void
    {
        ['election' => $election, 'categories' => $categories, 'candidates' => $candidates] = $this->createElectionBallot(1);
        $student = User::factory()->create();

        Vote::query()->create([
            'user_id' => $student->id,
            'election_id' => $election->id,
            'election_category_id' => $categories[0]->id,
            'candidate_id' => $candidates[0]->id,
            'voted_at' => now(),
        ]);

        app(ElectionSetupService::class)->syncOnUpdate($election, [
            'existing_positions' => [
                $categories[0]->id => ['name' => $categories[0]->name, 'remove' => '1'],
            ],
        ]);

        $this->assertDatabaseHas('election_categories', ['id' => $categories[0]->id]);
        $this->assertDatabaseHas('votes', ['election_category_id' => $categories[0]->id]);
    }

    public function test_scheduler_opens_drafts_when_voting_start_has_elapsed(): void
    {
        User::factory()->superAdmin()->create();
        $student = User::factory()->create();
        $election = Election::factory()->draft()->create([
            'title' => 'Due Draft Election',
            'voting_starts_at' => now()->subMinute(),
            'scheduled_open_at' => null,
        ]);

        Artisan::call('portal:process-scheduled-elections');

        $this->assertSame(ElectionStatus::Active, $election->fresh()->status);
        $this->assertDatabaseHas('portal_notifications', [
            'user_id' => $student->id,
            'type' => 'student_voting_open',
            'related_id' => $election->id,
        ]);
    }
}
