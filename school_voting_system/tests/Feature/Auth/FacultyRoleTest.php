<?php

namespace Tests\Feature\Auth;

use App\Enums\TalentEventStatus;
use App\Enums\TalentJudgeRole;
use App\Enums\TalentVotingMethod;
use App\Enums\UserRole;
use App\Models\Election;
use App\Models\Passkey;
use App\Models\TalentEvent;
use App\Models\User;
use App\Services\Auth\RoleRedirectService;
use App\Services\Talent\TalentJudgingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class FacultyRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_faculty_role_helpers_and_redirect(): void
    {
        $faculty = User::factory()->faculty()->create();

        $this->assertTrue($faculty->isFaculty());
        $this->assertFalse($faculty->canVote());
        $this->assertSame('Faculty', $faculty->roleLabel());
        $this->assertSame('/faculty/dashboard', app(RoleRedirectService::class)->dashboardPathFor($faculty));
        $this->assertSame(UserRole::Faculty, $faculty->role);
    }

    public function test_faculty_can_view_dashboard_without_breeze_nav(): void
    {
        $faculty = User::factory()->faculty()->create(['name' => 'Faculty Tester']);

        $response = $this->actingAs($faculty)->get(route('faculty.dashboard'));

        $response->assertOk();
        $response->assertSee('Welcome back, Faculty Tester');
        $response->assertSee('Faculty Portal');
        $response->assertDontSee('bg-white border-b border-gray-100', false);
    }

    public function test_super_admin_can_open_faculty_list(): void
    {
        $super = User::factory()->superAdmin()->create();
        User::factory()->faculty()->create(['account_id' => 'FACULTY-100']);

        $this->actingAs($super)
            ->get(route('super-admin.faculty.index'))
            ->assertOk()
            ->assertSee('FACULTY-100');
    }

    public function test_logged_in_super_admin_can_open_enrollment_link(): void
    {
        $super = User::factory()->superAdmin()->create();
        $faculty = User::factory()->faculty()->create(['account_id' => 'FACULTY-861']);

        $url = URL::temporarySignedRoute(
            'register.passkey.bootstrap',
            now()->addMinutes(120),
            ['user' => $faculty->id],
        );

        $response = $this->actingAs($super)->get($url);

        $response->assertOk();
        $response->assertSee('Register your passkey');
        $response->assertSee('FACULTY-861');
        $this->assertGuest();
    }

    public function test_faculty_settings_hides_grade_and_section(): void
    {
        $faculty = User::factory()->faculty()->create([
            'account_id' => 'FACULTY-861',
            'is_active' => true,
            'grade_level' => '10',
            'section' => 'A',
        ]);

        $response = $this->actingAs($faculty)->get(route('profile.edit', ['section' => 'profile']));

        $response->assertOk();
        $response->assertSee('Assigned Competitions');
        $response->assertSee('None Assigned');
        $response->assertSee('from-teal-500 to-emerald-400', false);
        $response->assertDontSee('Grade / Section');
        $response->assertDontSee('Grade and section are managed by the school administration.');
    }

    public function test_faculty_settings_counts_current_assignments_only(): void
    {
        $faculty = $this->withPasskey(User::factory()->faculty()->create());
        $admin = User::factory()->superAdmin()->create();
        $judging = app(TalentJudgingService::class);

        $current = $this->makeCompetition([
            'title' => 'Live Showcase',
            'slug' => 'live-showcase',
        ]);
        $past = $this->makeCompetition([
            'title' => 'Finished Showcase',
            'slug' => 'finished-showcase',
            'status' => TalentEventStatus::VotingOpen,
            'voting_starts_at' => now()->subDays(2),
            'voting_ends_at' => now()->subHour(),
        ]);

        $judging->assignJudge($current, $faculty, $admin, TalentJudgeRole::Judge);
        $judging->assignJudge($past, $faculty, $admin, TalentJudgeRole::Judge);

        $this->actingAs($faculty)
            ->get(route('profile.edit', ['section' => 'profile']))
            ->assertOk()
            ->assertViewHas('currentAssignedCompetitionsCount', 1)
            ->assertSee('Assigned Competitions')
            ->assertDontSee('None Assigned');
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
