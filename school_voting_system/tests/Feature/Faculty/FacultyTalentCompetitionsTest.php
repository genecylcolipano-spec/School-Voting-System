<?php

namespace Tests\Feature\Faculty;

use App\Enums\TalentEventStatus;
use App\Enums\TalentJudgeRole;
use App\Enums\TalentVotingMethod;
use App\Models\Election;
use App\Models\Passkey;
use App\Models\TalentEvent;
use App\Models\User;
use App\Services\Talent\TalentJudgingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacultyTalentCompetitionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_unassigned_faculty_can_browse_published_competitions_only(): void
    {
        $faculty = User::factory()->faculty()->create();
        $visible = $this->makeCompetition([
            'title' => 'Published Showcase',
            'slug' => 'published-showcase',
        ]);
        $this->makeCompetition([
            'title' => 'Unpublished Showcase',
            'slug' => 'unpublished-showcase',
            'published_to_students' => false,
        ]);

        $this->actingAs($faculty)
            ->get(route('faculty.talent.index'))
            ->assertOk()
            ->assertSee('Published Showcase')
            ->assertDontSee('Unpublished Showcase')
            ->assertSee('Faculty accounts are view-only')
            ->assertDontSee('Assigned Competitions')
            ->assertDontSee('Judge Performances')
            ->assertDontSee('Vote Now');

        $this->actingAs($faculty)
            ->get(route('faculty.talent.show', $visible))
            ->assertOk()
            ->assertSee('Published Showcase')
            ->assertSee('View only')
            ->assertDontSee('Judge this competition');

        $this->actingAs($faculty)
            ->get(route('faculty.talent.show', 'unpublished-showcase'))
            ->assertNotFound();
    }

    public function test_assigned_faculty_can_open_judging_from_view_only_page(): void
    {
        $faculty = $this->withPasskey(User::factory()->faculty()->create());
        $admin = User::factory()->superAdmin()->create();
        $event = $this->makeCompetition([
            'title' => 'Judgeable Showcase',
            'slug' => 'judgeable-showcase',
        ]);
        app(TalentJudgingService::class)->assignJudge($event, $faculty, $admin, TalentJudgeRole::Judge);

        $this->actingAs($faculty)
            ->get(route('faculty.talent.show', $event))
            ->assertOk()
            ->assertSee('Judge this competition')
            ->assertSee(route('faculty.judging.show', $event), false);

        $this->actingAs($faculty)
            ->get(route('faculty.dashboard'))
            ->assertOk()
            ->assertSee('My Judging')
            ->assertSee('Talent Competitions');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeCompetition(array $overrides = []): TalentEvent
    {
        $election = Election::factory()->create();

        return TalentEvent::query()->create(array_merge([
            'election_id' => $election->id,
            'title' => 'Campus Talent Night',
            'slug' => 'campus-talent-night-'.uniqid(),
            'event_date' => now()->addDay(),
            'venue' => 'Auditorium',
            'status' => TalentEventStatus::VotingOpen,
            'voting_method' => TalentVotingMethod::JudgesOnly->value,
            'voting_starts_at' => now()->subHour(),
            'voting_ends_at' => now()->addDay(),
            'published_to_students' => true,
            'created_by' => User::factory()->admin()->create()->id,
        ], $overrides));
    }

    protected function withPasskey(User $faculty): User
    {
        $passkey = new Passkey([
            'name' => 'Test Device',
            'credential_id' => 'cred-'.$faculty->id.'-'.uniqid(),
            'credential' => ['type' => 'public-key'],
            'counter' => 0,
        ]);
        $passkey->user_id = $faculty->id;
        $passkey->save();

        return $faculty;
    }
}
