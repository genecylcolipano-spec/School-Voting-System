<?php

namespace Tests\Feature\Admin;

use App\Enums\ElectionStatus;
use App\Enums\EventStatus;
use App\Enums\TalentEventStatus;
use App\Enums\TalentRegistrationMethod;
use App\Enums\TalentVotingMethod;
use App\Mail\AnnouncementPublishedMail;
use App\Models\AdminAssignment;
use App\Models\Announcement;
use App\Models\Election;
use App\Models\Event;
use App\Models\PortalNotification;
use App\Models\TalentEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EventsTalentDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_regular_admin_sees_own_talent_competitions_even_when_election_id_differs(): void
    {
        $admin = User::factory()->admin()->create();
        $assigned = Election::factory()->create([
            'status' => ElectionStatus::Active,
            'created_by' => $admin->id,
        ]);
        $previous = Election::factory()->create([
            'status' => ElectionStatus::Closed,
            'created_by' => $admin->id,
        ]);

        AdminAssignment::query()->create([
            'user_id' => $admin->id,
            'election_id' => $assigned->id,
            'assigned_by' => $admin->id,
        ]);

        $ownCompetition = $this->makeCompetition($previous, $admin, 'Campus Idol');
        $assignedCompetition = $this->makeCompetition(
            $assigned,
            User::factory()->admin()->create(),
            'Assigned Quiz Bowl',
        );
        $outOfScope = $this->makeCompetition(
            $previous,
            User::factory()->admin()->create(),
            'Other School Showcase',
        );

        $this->actingAs($admin)
            ->get(route('admin.events-talent.index'))
            ->assertOk()
            ->assertSee('Campus Idol')
            ->assertSee('Assigned Quiz Bowl')
            ->assertDontSee('Other School Showcase')
            ->assertDontSee('No talent events yet.');

        $this->assertNotNull($ownCompetition->id);
        $this->assertNotNull($assignedCompetition->id);
        $this->assertNotNull($outOfScope->id);
    }

    public function test_dashboard_live_monitoring_hides_soft_deleted_assigned_election(): void
    {
        $admin = User::factory()->admin()->create();
        $election = Election::factory()->active()->create([
            'title' => 'Vanished Live Council',
            'created_by' => $admin->id,
        ]);

        AdminAssignment::query()->create([
            'user_id' => $admin->id,
            'election_id' => $election->id,
            'assigned_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Vanished Live Council');

        $election->delete();

        $html = $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/id="live-voting-election-title"[^>]*>\s*No assigned election/',
            $html,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/id="live-voting-election-title"[^>]*>\s*Vanished Live Council/',
            $html,
        );
    }

    public function test_super_admin_sees_all_talent_competitions_on_events_dashboard(): void
    {
        $super = User::factory()->superAdmin()->create();
        $election = Election::factory()->create();
        $this->makeCompetition($election, User::factory()->admin()->create(), 'Hidden From Regular Filter');

        $this->actingAs($super)
            ->get(route('admin.events-talent.index'))
            ->assertOk()
            ->assertSee('Hidden From Regular Filter');
    }

    public function test_events_dashboard_uses_unread_notification_count_and_manage_opens_workspace(): void
    {
        $super = User::factory()->superAdmin()->create();
        $election = Election::factory()->create(['created_by' => $super->id]);
        $competition = $this->makeCompetition($election, $super, 'Workspace Showcase');

        PortalNotification::query()->create([
            'title' => 'Event ping',
            'message' => 'Unread for events',
            'type' => 'info',
            'user_id' => $super->id,
            'recipient_role' => 'super_admin',
        ]);

        $this->actingAs($super)
            ->get(route('admin.events-talent.index'))
            ->assertOk()
            ->assertSee('data-initial-count="1"', false)
            ->assertSee('Open competitions')
            ->assertSee(route('admin.talent-competition.show', $competition), false)
            ->assertSee('>Manage<', false);
    }

    public function test_regular_admin_does_not_see_another_admins_school_events(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->admin()->create();

        $this->makeSchoolEvent($admin, 'My Orientation');
        $this->makeSchoolEvent($other, 'Other Campus Fair');

        $this->actingAs($admin)
            ->get(route('admin.events-talent.index'))
            ->assertOk()
            ->assertSee('My Orientation')
            ->assertDontSee('Other Campus Fair');

        $this->actingAs($admin)
            ->get(route('admin.events.index'))
            ->assertOk()
            ->assertSee('My Orientation')
            ->assertDontSee('Other Campus Fair');
    }

    public function test_super_admin_sees_every_school_event_on_events_dashboard(): void
    {
        $super = User::factory()->superAdmin()->create();
        $this->makeSchoolEvent(User::factory()->admin()->create(), 'Campus-Wide Assembly');

        $this->actingAs($super)
            ->get(route('admin.events-talent.index'))
            ->assertOk()
            ->assertSee('Campus-Wide Assembly');
    }

    public function test_opening_talent_registration_notifies_students_in_app_not_email(): void
    {
        Mail::fake();

        $super = User::factory()->superAdmin()->create();
        $student = User::factory()->create();
        $faculty = User::factory()->faculty()->create();
        $election = Election::factory()->create([
            'status' => ElectionStatus::Active,
            'created_by' => $super->id,
        ]);
        $event = TalentEvent::query()->create([
            'election_id' => $election->id,
            'title' => 'Chorus Showcase',
            'slug' => 'chorus-showcase-'.uniqid(),
            'event_date' => now()->addDays(8),
            'venue' => 'Online',
            'status' => TalentEventStatus::Scheduled,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'registration_method' => TalentRegistrationMethod::Both,
            'voting_starts_at' => now()->addDays(8),
            'voting_ends_at' => now()->addDays(10),
            'published_to_students' => false,
            'created_by' => $super->id,
        ]);

        $this->actingAs($super)
            ->from(route('admin.talent-competition.show', $event))
            ->post(route('admin.talent-competition.open-registration', $event))
            ->assertRedirect(route('admin.talent-competition.show', $event));

        $this->actingAs($super)
            ->from(route('admin.talent-competition.show', $event))
            ->post(route('admin.talent-competition.open-registration', $event))
            ->assertRedirect(route('admin.talent-competition.show', $event));

        Mail::assertNothingOutgoing();
        Mail::assertNotQueued(AnnouncementPublishedMail::class);

        $this->assertTrue($event->fresh()->isRegistrationOpen());
        $this->assertTrue(
            Announcement::query()
                ->where('auto_source_type', 'talent_registration_open')
                ->where('auto_source_id', $event->id)
                ->where('send_email', false)
                ->exists()
        );
        $this->assertSame(
            1,
            PortalNotification::query()
                ->where('user_id', $student->id)
                ->where('type', 'student_talent_registration_open')
                ->where('related_id', $event->id)
                ->count()
        );
        $this->assertFalse(
            PortalNotification::query()
                ->where('user_id', $faculty->id)
                ->where('type', 'student_talent_registration_open')
                ->exists()
        );
    }

    protected function makeSchoolEvent(User $creator, string $title): Event
    {
        return Event::query()->create([
            'title' => $title,
            'slug' => str($title)->slug().'-'.uniqid(),
            'event_date' => now()->addDays(3),
            'venue' => 'Auditorium',
            'status' => EventStatus::Scheduled,
            'created_by' => $creator->id,
        ]);
    }

    protected function makeCompetition(Election $election, User $creator, string $title): TalentEvent
    {
        return TalentEvent::query()->create([
            'election_id' => $election->id,
            'title' => $title,
            'slug' => str($title)->slug().'-'.uniqid(),
            'event_date' => now()->addDay(),
            'venue' => 'Online',
            'status' => TalentEventStatus::VotingOpen,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'voting_starts_at' => now()->subHour(),
            'voting_ends_at' => now()->addDay(),
            'published_to_students' => true,
            'created_by' => $creator->id,
        ]);
    }
}
