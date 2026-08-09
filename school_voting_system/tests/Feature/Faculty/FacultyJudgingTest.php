<?php

namespace Tests\Feature\Faculty;

use App\Enums\TalentEventStatus;
use App\Enums\TalentJudgeRole;
use App\Enums\TalentJudgeScoreStatus;
use App\Enums\TalentVotingMethod;
use App\Models\Election;
use App\Models\Passkey;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\TalentJudgeScoreSheet;
use App\Models\User;
use App\Services\Talent\TalentJudgingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacultyJudgingTest extends TestCase
{
    use RefreshDatabase;

    protected function makeCompetition(array $overrides = []): TalentEvent
    {
        $election = Election::factory()->create();

        return TalentEvent::query()->create(array_merge([
            'election_id' => $election->id,
            'title' => 'Campus Talent Night',
            'slug' => 'campus-talent-night',
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

    protected function makeApprovedEntry(TalentEvent $event): TalentEventEntry
    {
        return TalentEventEntry::query()->create([
            'talent_event_id' => $event->id,
            'display_name' => 'Ava Performer',
            'performance_title' => 'Vocal Solo',
            'status' => TalentEventEntry::STATUS_APPROVED,
            'source' => TalentEventEntry::SOURCE_ADMIN,
        ]);
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

    public function test_faculty_sees_assigned_competition_and_can_submit_scores(): void
    {
        $faculty = $this->withPasskey(User::factory()->faculty()->create());
        $admin = User::factory()->superAdmin()->create();
        $event = $this->makeCompetition();
        $entry = $this->makeApprovedEntry($event);

        $judging = app(TalentJudgingService::class);
        $judging->assignJudge($event, $faculty, $admin, TalentJudgeRole::HeadJudge);
        $judging->ensureDefaultCriteria($event);

        $this->actingAs($faculty)
            ->get(route('faculty.judging.index'))
            ->assertOk()
            ->assertSee('Campus Talent Night')
            ->assertSee('Lead Judge');

        $this->actingAs($faculty)
            ->get(route('faculty.judging.show', $event))
            ->assertOk()
            ->assertSee('Ava Performer');

        $criteria = $event->judgingCriteria()->orderBy('sort_order')->get();
        $scores = [];
        foreach ($criteria as $criterion) {
            $scores[$criterion->id] = 20;
        }

        $this->actingAs($faculty)
            ->post(route('faculty.judging.submit', [$event, $entry]), [
                'scores' => $scores,
                'notes' => 'Strong stage presence',
            ])
            ->assertRedirect(route('faculty.judging.show', $event));

        $sheet = TalentJudgeScoreSheet::query()->first();
        $this->assertNotNull($sheet);
        $this->assertSame(TalentJudgeScoreStatus::Submitted, $sheet->status);
        $this->assertEquals(80.0, (float) $sheet->total_score);

        $this->actingAs($faculty)
            ->get(route('faculty.judging.submitted'))
            ->assertOk()
            ->assertSee('Campus Talent Night')
            ->assertSee('Lead Judge');
    }

    public function test_unassigned_faculty_cannot_open_competition(): void
    {
        $faculty = User::factory()->faculty()->create();
        $event = $this->makeCompetition();

        $this->actingAs($faculty)
            ->get(route('faculty.judging.show', $event))
            ->assertForbidden();
    }

    public function test_submitted_scores_are_locked(): void
    {
        $faculty = $this->withPasskey(User::factory()->faculty()->create());
        $admin = User::factory()->superAdmin()->create();
        $event = $this->makeCompetition();
        $entry = $this->makeApprovedEntry($event);

        $judging = app(TalentJudgingService::class);
        $judging->assignJudge($event, $faculty, $admin);
        $criteria = $event->fresh()->judgingCriteria()->orderBy('sort_order')->get();

        $scores = [];
        foreach ($criteria as $criterion) {
            $scores[$criterion->id] = 15;
        }

        $this->actingAs($faculty)
            ->post(route('faculty.judging.submit', [$event, $entry]), ['scores' => $scores])
            ->assertRedirect();

        foreach ($criteria as $criterion) {
            $scores[$criterion->id] = 25;
        }

        $this->actingAs($faculty)
            ->post(route('faculty.judging.submit', [$event, $entry]), ['scores' => $scores])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertEquals(60.0, (float) TalentJudgeScoreSheet::query()->first()->total_score);
    }

    public function test_super_admin_can_assign_faculty_judge(): void
    {
        $super = User::factory()->superAdmin()->create();
        $faculty = $this->withPasskey(User::factory()->faculty()->create(['name' => 'Judge Pat']));
        $event = $this->makeCompetition();

        $this->actingAs($super)
            ->post(route('super-admin.faculty.competitions.assign', $faculty), [
                'talent_event_id' => $event->id,
                'judge_role' => TalentJudgeRole::Judge->value,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('talent_event_judges', [
            'talent_event_id' => $event->id,
            'user_id' => $faculty->id,
            'judge_role' => TalentJudgeRole::Judge->value,
            'status' => 'active',
        ]);

        $this->actingAs($super)
            ->get(route('admin.talent-competition.judges', $event))
            ->assertOk()
            ->assertSee('Judge Pat');
    }

    public function test_student_only_competition_appears_in_faculty_assign_dropdown(): void
    {
        $super = User::factory()->superAdmin()->create();
        $faculty = $this->withPasskey(User::factory()->faculty()->create());
        $event = $this->makeCompetition([
            'title' => 'Rosemont Showcase',
            'slug' => 'rosemont-showcase-assign',
            'status' => TalentEventStatus::Scheduled,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'voting_starts_at' => now()->addDay(),
            'voting_ends_at' => now()->addDays(2),
        ]);

        $this->actingAs($super)
            ->get(route('super-admin.faculty.show', $faculty))
            ->assertOk()
            ->assertSee('Rosemont Showcase')
            ->assertSee('Select competition');

        $this->actingAs($super)
            ->post(route('super-admin.faculty.competitions.assign', $faculty), [
                'talent_event_id' => $event->id,
                'judge_role' => TalentJudgeRole::HeadJudge->value,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('talent_event_judges', [
            'talent_event_id' => $event->id,
            'user_id' => $faculty->id,
            'judge_role' => TalentJudgeRole::HeadJudge->value,
            'status' => 'active',
        ]);

        $this->assertSame(
            TalentVotingMethod::JudgesAndStudents,
            $event->fresh()->voting_method
        );

        $this->actingAs($faculty)
            ->get(route('faculty.dashboard'))
            ->assertOk()
            ->assertSee('Rosemont Showcase')
            ->assertSee('Lead Judge');
    }

    public function test_election_admin_cannot_assign_faculty_judge(): void
    {
        $admin = User::factory()->admin()->create();
        $faculty = $this->withPasskey(User::factory()->faculty()->create());
        $event = $this->makeCompetition(['created_by' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('admin.talent-competition.judges.assign', $event), [
                'user_id' => $faculty->id,
                'judge_role' => TalentJudgeRole::Judge->value,
            ])
            ->assertForbidden();
    }

    public function test_removed_assignment_blocks_faculty_access(): void
    {
        $faculty = $this->withPasskey(User::factory()->faculty()->create());
        $super = User::factory()->superAdmin()->create();
        $event = $this->makeCompetition();

        $judging = app(TalentJudgingService::class);
        $judging->assignJudge($event, $faculty, $super);
        $judging->removeJudge($event, $faculty, $super, 'Schedule conflict');

        $this->actingAs($faculty)
            ->get(route('faculty.judging.show', $event))
            ->assertForbidden();
    }

    public function test_student_cannot_access_faculty_judging(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)
            ->get(route('faculty.judging.index'))
            ->assertForbidden();
    }
}
