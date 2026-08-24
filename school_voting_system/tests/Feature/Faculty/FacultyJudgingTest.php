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

    protected function makeApprovedEntry(TalentEvent $event, array $overrides = []): TalentEventEntry
    {
        return TalentEventEntry::query()->create(array_merge([
            'talent_event_id' => $event->id,
            'display_name' => 'Ava Performer',
            'performance_title' => 'Vocal Solo',
            'status' => TalentEventEntry::STATUS_APPROVED,
            'source' => TalentEventEntry::SOURCE_ADMIN,
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
            ->assertSee('Lead Judge')
            ->assertSee('Open Judging')
            ->assertSee('Judging open')
            ->assertDontSee('View Participants')
            ->assertDontSee('Registration Open')
            ->assertDontSee('Registration Closed');

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
            ->assertSee('Lead Judge')
            ->assertSee('Complete')
            ->assertSee('Ava Performer')
            ->assertSee(route('faculty.judging.score', [$event, $entry, 'from' => 'submitted']), false);

        $this->actingAs($faculty)
            ->get(route('faculty.judging.score', [$event, $entry, 'from' => 'submitted']))
            ->assertOk()
            ->assertSee('&larr; Submitted Scores', false);
    }

    public function test_submitted_scores_hide_sheets_from_deleted_competitions(): void
    {
        $faculty = $this->withPasskey(User::factory()->faculty()->create());
        $admin = User::factory()->superAdmin()->create();
        $judging = app(TalentJudgingService::class);

        $live = $this->makeCompetition([
            'title' => 'Live Showcase',
            'slug' => 'live-showcase-submitted',
        ]);
        $removed = $this->makeCompetition([
            'title' => 'Removed Showcase',
            'slug' => 'removed-showcase-submitted',
        ]);
        $liveEntry = $this->makeApprovedEntry($live, ['display_name' => 'Live Performer']);
        $removedEntry = $this->makeApprovedEntry($removed, ['display_name' => 'Orphaned Performer']);

        $judging->assignJudge($live, $faculty, $admin);
        $judging->assignJudge($removed, $faculty, $admin);
        $judging->ensureDefaultCriteria($live);
        $judging->ensureDefaultCriteria($removed);

        $liveScores = [];
        foreach ($live->fresh()->judgingCriteria()->orderBy('sort_order')->get() as $criterion) {
            $liveScores[$criterion->id] = 20;
        }
        $removedScores = [];
        foreach ($removed->fresh()->judgingCriteria()->orderBy('sort_order')->get() as $criterion) {
            $removedScores[$criterion->id] = 15;
        }

        $this->actingAs($faculty)
            ->post(route('faculty.judging.submit', [$live, $liveEntry]), ['scores' => $liveScores])
            ->assertRedirect();
        $this->actingAs($faculty)
            ->post(route('faculty.judging.submit', [$removed, $removedEntry]), ['scores' => $removedScores])
            ->assertRedirect();

        $removed->delete();

        $this->actingAs($faculty)
            ->get(route('faculty.judging.submitted'))
            ->assertOk()
            ->assertSee('Live Showcase')
            ->assertSee('Live Performer')
            ->assertDontSee('Removed Showcase')
            ->assertDontSee('Orphaned Performer');
    }

    public function test_assigned_list_shows_judging_status_not_registration(): void
    {
        $faculty = $this->withPasskey(User::factory()->faculty()->create());
        $admin = User::factory()->superAdmin()->create();
        $judging = app(TalentJudgingService::class);

        $open = $this->makeCompetition([
            'title' => 'Open Judging Night',
            'slug' => 'open-judging-night',
        ]);
        $scheduled = $this->makeCompetition([
            'title' => 'Scheduled Judging Night',
            'slug' => 'scheduled-judging-night',
            'status' => TalentEventStatus::Scheduled,
            'voting_starts_at' => now()->addDay(),
            'voting_ends_at' => now()->addDays(2),
            'registration_starts_at' => now()->subHour(),
            'registration_ends_at' => now()->addDay(),
        ]);
        $ended = $this->makeCompetition([
            'title' => 'Ended Judging Night',
            'slug' => 'ended-judging-night',
            'status' => TalentEventStatus::VotingOpen,
            'voting_starts_at' => now()->subDays(2),
            'voting_ends_at' => now()->subHour(),
        ]);

        $judging->assignJudge($open, $faculty, $admin);
        $judging->assignJudge($scheduled, $faculty, $admin);
        $judging->assignJudge($ended, $faculty, $admin);

        $this->assertTrue($scheduled->fresh()->isRegistrationOpen());
        $this->assertSame('scheduled', $scheduled->fresh()->judgingPhaseKey());
        $this->assertSame('closed', $ended->fresh()->judgingPhaseKey());

        $this->actingAs($faculty)
            ->get(route('faculty.judging.index'))
            ->assertOk()
            ->assertSee('Current')
            ->assertSee('Past')
            ->assertSee('Open Judging Night')
            ->assertSee('Scheduled Judging Night')
            ->assertDontSee('Ended Judging Night')
            ->assertSee('Judging open')
            ->assertSee('Judging scheduled')
            ->assertSee('Opens')
            ->assertSee('Closes')
            ->assertDontSee('Registration Open')
            ->assertDontSee('View Participants');

        $this->actingAs($faculty)
            ->get(route('faculty.judging.index', ['filter' => 'past']))
            ->assertOk()
            ->assertSee('Ended Judging Night')
            ->assertSee('Judging closed')
            ->assertSee('Under administrator review')
            ->assertDontSee('View official results')
            ->assertDontSee('Open Judging Night')
            ->assertDontSee('Scheduled Judging Night');

        $this->actingAs($faculty)
            ->get(route('faculty.judging.performances'))
            ->assertOk()
            ->assertSee('Open Judging Night')
            ->assertSee('Scheduled Judging Night')
            ->assertSee('Closes')
            ->assertDontSee('Ended Judging Night');

        $this->actingAs($faculty)
            ->get(route('faculty.judging.show', $open))
            ->assertOk()
            ->assertSee('Judging open')
            ->assertSee('Closes');

        $this->actingAs($faculty)
            ->get(route('faculty.judging.show', $scheduled))
            ->assertOk()
            ->assertSee('Judging scheduled')
            ->assertSee('Opens');

        $this->actingAs($faculty)
            ->get(route('faculty.judging.show', $ended))
            ->assertOk()
            ->assertSee('Ended Judging Night')
            ->assertSee('Judging closed');
    }

    public function test_judge_performances_lists_current_performances_with_score_status(): void
    {
        $faculty = $this->withPasskey(User::factory()->faculty()->create());
        $admin = User::factory()->superAdmin()->create();
        $judging = app(TalentJudgingService::class);

        $open = $this->makeCompetition([
            'title' => 'Open Judging Night',
            'slug' => 'open-performances-night',
        ]);
        $ended = $this->makeCompetition([
            'title' => 'Ended Judging Night',
            'slug' => 'ended-performances-night',
            'voting_starts_at' => now()->subDays(2),
            'voting_ends_at' => now()->subHour(),
        ]);

        $pending = $this->makeApprovedEntry($open, [
            'display_name' => 'Pending Singer',
            'performance_title' => 'Ballad',
        ]);
        $drafted = $this->makeApprovedEntry($open, [
            'display_name' => 'Draft Dancer',
            'performance_title' => 'Contemporary',
        ]);
        $submitted = $this->makeApprovedEntry($open, [
            'display_name' => 'Submitted Vocalist',
            'performance_title' => 'Aria',
        ]);
        $this->makeApprovedEntry($ended, [
            'display_name' => 'Past Performer',
            'performance_title' => 'Closed Act',
        ]);

        $judging->assignJudge($open, $faculty, $admin);
        $judging->assignJudge($ended, $faculty, $admin);
        $judging->ensureDefaultCriteria($open);

        $criteria = $open->fresh()->judgingCriteria()->orderBy('sort_order')->get();
        $scores = [];
        foreach ($criteria as $criterion) {
            $scores[$criterion->id] = 18;
        }

        $this->actingAs($faculty)
            ->post(route('faculty.judging.draft', [$open, $drafted]), [
                'scores' => $scores,
            ])
            ->assertRedirect();

        $this->actingAs($faculty)
            ->post(route('faculty.judging.submit', [$open, $submitted]), [
                'scores' => $scores,
            ])
            ->assertRedirect();

        $this->actingAs($faculty)
            ->get(route('faculty.judging.performances'))
            ->assertOk()
            ->assertSee('Open Judging Night')
            ->assertSee('Pending Singer')
            ->assertSee('Draft Dancer')
            ->assertSee('Submitted Vocalist')
            ->assertSee('Ballad')
            ->assertSee('Not started')
            ->assertSee('Draft')
            ->assertSee('Submitted')
            ->assertSee('Score')
            ->assertSee('Continue')
            ->assertSee('View scores')
            ->assertSee(route('faculty.judging.score', [$open, $pending, 'from' => 'performances']), false)
            ->assertSee('View profile')
            ->assertDontSee('Ended Judging Night')
            ->assertDontSee('Past Performer')
            ->assertDontSee('Continue judging')
            ->assertDontSee('Vote');
    }

    public function test_unassigned_faculty_cannot_open_competition(): void
    {
        $faculty = User::factory()->faculty()->create();
        $event = $this->makeCompetition();

        $this->actingAs($faculty)
            ->get(route('faculty.judging.show', $event))
            ->assertForbidden();
    }

    public function test_faculty_can_view_assigned_performance_profile_without_voting(): void
    {
        $faculty = $this->withPasskey(User::factory()->faculty()->create());
        $other = User::factory()->faculty()->create();
        $admin = User::factory()->superAdmin()->create();
        $event = $this->makeCompetition();
        $entry = $this->makeApprovedEntry($event, [
            'display_name' => 'Profile Performer',
            'performance_title' => 'Original Song',
            'grade_level' => '3rd Year',
            'section' => '4A',
            'profile_summary' => 'I love singing on stage.',
            'student_id_number' => 'STU-SECRET-99',
        ]);

        app(TalentJudgingService::class)->assignJudge($event, $faculty, $admin);

        $this->actingAs($faculty)
            ->get(route('faculty.judging.profile', [$event, $entry, 'from' => 'performances']))
            ->assertOk()
            ->assertSee('Profile Performer')
            ->assertSee('Original Song')
            ->assertSee('I love singing on stage.')
            ->assertSee('3rd Year')
            ->assertSee('4A')
            ->assertSee('Score')
            ->assertSee('Judge Performances')
            ->assertDontSee('Vote')
            ->assertDontSee('STU-SECRET-99');

        $this->actingAs($other)
            ->get(route('faculty.judging.profile', [$event, $entry]))
            ->assertForbidden();

        $this->actingAs(User::factory()->create())
            ->get(route('faculty.judging.profile', [$event, $entry]))
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
