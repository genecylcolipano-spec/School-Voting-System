<?php

namespace Tests\Unit\Portal;

use App\Enums\ElectionStatus;
use App\Enums\TalentEventStatus;
use App\Enums\TalentRegistrationMethod;
use App\Enums\TalentVotingMethod;
use App\Jobs\SendTalentVotingClosingSoonJob;
use App\Mail\AnnouncementPublishedMail;
use App\Models\Announcement;
use App\Models\Election;
use App\Models\PortalNotification;
use App\Models\TalentEvent;
use App\Models\User;
use App\Services\Portal\AnnouncementService;
use App\Services\Portal\PortalNotificationService;
use App\Services\SuperAdmin\ElectionLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PortalNotificationDeliveryTest extends TestCase
{
    use RefreshDatabase;

    protected PortalNotificationService $notifications;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notifications = app(PortalNotificationService::class);
    }

    public function test_election_created_notifies_assigned_admin_after_assignment(): void
    {
        $super = User::factory()->superAdmin()->create();
        $admin = User::factory()->admin()->create();

        $election = Election::factory()->create([
            'title' => 'Notify Order Election',
            'status' => ElectionStatus::Active,
            'created_by' => $admin->id,
        ]);

        app(\App\Services\Admin\AdminScopeService::class)
            ->assignElectionToAdmin($admin, $election, $admin->id);

        $this->notifications->electionCreated($election, $admin);

        $this->assertTrue(
            PortalNotification::query()
                ->where('user_id', $admin->id)
                ->where('type', 'admin_election_created')
                ->exists()
        );

        $this->assertTrue(
            PortalNotification::query()
                ->where('user_id', $super->id)
                ->where('type', 'admin_election_created')
                ->exists()
        );
    }

    public function test_fundraiser_created_skips_students_when_not_accepting(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();
        $faculty = User::factory()->faculty()->create();

        $this->notifications->fundraiserCreated('Draft Drive', $admin, 1, false);

        $this->assertTrue(
            PortalNotification::query()
                ->where('user_id', $admin->id)
                ->where('type', 'admin_fundraiser_created')
                ->exists()
        );

        $this->assertFalse(
            PortalNotification::query()
                ->where('user_id', $student->id)
                ->where('type', 'student_fundraiser_published')
                ->exists()
        );
        $this->assertFalse(
            PortalNotification::query()
                ->where('user_id', $faculty->id)
                ->where('type', 'faculty_fundraiser_published')
                ->exists()
        );
    }

    public function test_fundraiser_created_notifies_students_when_accepting(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();
        $faculty = User::factory()->faculty()->create();

        $this->notifications->fundraiserCreated('Live Drive', $admin, 2, true);

        $this->assertTrue(
            PortalNotification::query()
                ->where('user_id', $student->id)
                ->where('type', 'student_fundraiser_published')
                ->exists()
        );
        $this->assertTrue(
            PortalNotification::query()
                ->where('user_id', $faculty->id)
                ->where('type', 'faculty_fundraiser_published')
                ->exists()
        );
    }

    public function test_voting_resumed_notifies_students(): void
    {
        $super = User::factory()->superAdmin()->create();
        $student = User::factory()->create();
        $election = Election::factory()->active()->create();

        $this->notifications->votingResumed($election, $super);

        $this->assertTrue(
            PortalNotification::query()
                ->where('user_id', $student->id)
                ->where('type', 'student_voting_resumed')
                ->exists()
        );
    }

    public function test_voting_paused_notifies_students(): void
    {
        $super = User::factory()->superAdmin()->create();
        $student = User::factory()->create();
        $election = Election::factory()->active()->create();

        $this->notifications->votingPaused($election, $super);

        $this->assertTrue(
            PortalNotification::query()
                ->where('user_id', $student->id)
                ->where('type', 'student_voting_paused')
                ->exists()
        );
    }

    public function test_open_on_paused_active_election_resumes_instead_of_reopening(): void
    {
        $super = User::factory()->superAdmin()->create();
        $student = User::factory()->create();
        $election = Election::factory()->active()->create(['is_paused' => true]);

        app(ElectionLifecycleService::class)->open($election, $super);

        $this->assertFalse($election->fresh()->is_paused);
        $this->assertTrue(
            PortalNotification::query()
                ->where('user_id', $student->id)
                ->where('type', 'student_voting_resumed')
                ->exists()
        );
        $this->assertFalse(
            PortalNotification::query()
                ->where('user_id', $student->id)
                ->where('type', 'student_voting_open')
                ->exists()
        );
    }

    public function test_role_fan_out_inserts_immediately_without_a_queue_worker(): void
    {
        Queue::fake();

        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();
        $faculty = User::factory()->faculty()->create();
        $election = Election::factory()->closed()->create(['title' => 'Immediate Bell Election']);

        $this->notifications->resultsPublished($election, $admin);

        Queue::assertNothingPushed();
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
    }

    public function test_talent_voting_paused_notifies_students(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();
        $election = Election::factory()->create();

        $event = TalentEvent::query()->create([
            'election_id' => $election->id,
            'title' => 'Paused Showcase',
            'slug' => 'paused-'.Str::random(6),
            'event_date' => now()->addDay(),
            'status' => TalentEventStatus::VotingOpen,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'voting_starts_at' => now()->subHour(),
            'voting_ends_at' => now()->addHours(12),
            'published_to_students' => true,
            'created_by' => $admin->id,
        ]);

        $this->notifications->talentVotingPaused($event, $admin);

        $this->assertTrue(
            PortalNotification::query()
                ->where('user_id', $student->id)
                ->where('type', 'student_talent_voting_paused')
                ->exists()
        );
    }

    public function test_talent_registration_open_notifies_students_without_email(): void
    {
        Mail::fake();

        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();
        $faculty = User::factory()->faculty()->create();
        $election = Election::factory()->create(['created_by' => $admin->id]);

        $event = TalentEvent::query()->create([
            'election_id' => $election->id,
            'title' => 'Open Mic Night',
            'slug' => 'open-mic-'.Str::random(6),
            'event_date' => now()->addDay(),
            'status' => TalentEventStatus::EntriesOpen,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'registration_method' => TalentRegistrationMethod::Both,
            'registration_starts_at' => now()->subMinute(),
            'registration_ends_at' => now()->addDays(7),
            'voting_starts_at' => now()->addDays(8),
            'voting_ends_at' => now()->addDays(10),
            'published_to_students' => true,
            'created_by' => $admin->id,
        ]);

        $sent = $this->notifications->talentRegistrationOpened($event, $admin);
        $duplicate = $this->notifications->talentRegistrationOpened($event, $admin);

        $this->assertSame(1, $sent);
        $this->assertSame(0, $duplicate);
        Mail::assertNothingQueued();
        $this->assertSame(
            1,
            PortalNotification::query()
                ->where('type', 'student_talent_registration_open')
                ->where('related_id', $event->id)
                ->count()
        );
        $this->assertTrue(
            PortalNotification::query()
                ->where('user_id', $student->id)
                ->where('type', 'student_talent_registration_open')
                ->exists()
        );
        $this->assertFalse(
            PortalNotification::query()
                ->where('user_id', $faculty->id)
                ->where('type', 'student_talent_registration_open')
                ->exists()
        );

        $notification = PortalNotification::query()
            ->where('user_id', $student->id)
            ->where('type', 'student_talent_registration_open')
            ->first();

        $this->assertSame(
            route('student.talent-registration.show', $event),
            $this->notifications->actionUrlFor($notification)
        );
    }

    public function test_scheduled_command_notifies_when_talent_registration_window_opens(): void
    {
        Mail::fake();

        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();
        $election = Election::factory()->create();

        $event = TalentEvent::query()->create([
            'election_id' => $election->id,
            'title' => 'Scheduled Registration Showcase',
            'slug' => 'scheduled-reg-'.Str::random(6),
            'event_date' => now()->addDay(),
            'status' => TalentEventStatus::Scheduled,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'registration_method' => TalentRegistrationMethod::StudentRegistration,
            'registration_starts_at' => now()->subMinute(),
            'registration_ends_at' => now()->addDays(3),
            'voting_starts_at' => now()->addDays(4),
            'voting_ends_at' => now()->addDays(6),
            'published_to_students' => true,
            'created_by' => $admin->id,
        ]);

        $future = TalentEvent::query()->create([
            'election_id' => $election->id,
            'title' => 'Future Registration Showcase',
            'slug' => 'future-reg-'.Str::random(6),
            'event_date' => now()->addDays(10),
            'status' => TalentEventStatus::Scheduled,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'registration_method' => TalentRegistrationMethod::Both,
            'registration_starts_at' => now()->addDay(),
            'registration_ends_at' => now()->addDays(4),
            'voting_starts_at' => now()->addDays(5),
            'voting_ends_at' => now()->addDays(7),
            'published_to_students' => true,
            'created_by' => $admin->id,
        ]);

        Artisan::call('portal:process-scheduled-elections');
        Artisan::call('portal:process-scheduled-elections');

        Mail::assertNothingQueued();
        $this->assertTrue(
            PortalNotification::query()
                ->where('user_id', $student->id)
                ->where('type', 'student_talent_registration_open')
                ->where('related_id', $event->id)
                ->exists()
        );
        $this->assertFalse(
            PortalNotification::query()
                ->where('type', 'student_talent_registration_open')
                ->where('related_id', $future->id)
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
    }

    public function test_school_event_published_notifies_faculty_and_students(): void
    {
        $admin = User::factory()->admin()->create();
        $faculty = User::factory()->faculty()->create();
        $student = User::factory()->create();

        $this->notifications->schoolEventPublished('Intramurals 2026', $admin, 99);

        $this->assertTrue(
            PortalNotification::query()
                ->where('user_id', $faculty->id)
                ->where('type', 'faculty_event_published')
                ->exists()
        );
        $this->assertTrue(
            PortalNotification::query()
                ->where('user_id', $student->id)
                ->where('type', 'student_event_reminder')
                ->exists()
        );
    }

    public function test_talent_closing_soon_dispatches_once(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();
        $election = Election::factory()->create();

        $event = TalentEvent::query()->create([
            'election_id' => $election->id,
            'title' => 'Closing Soon Showcase',
            'slug' => 'closing-soon-'.Str::random(6),
            'event_date' => now()->addDay(),
            'status' => TalentEventStatus::VotingOpen,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'voting_starts_at' => now()->subHour(),
            'voting_ends_at' => now()->addHours(12),
            'published_to_students' => true,
            'created_by' => $admin->id,
        ]);

        $first = $this->notifications->dispatchTalentVotingClosingSoonReminders(24);
        $second = $this->notifications->dispatchTalentVotingClosingSoonReminders(24);

        $this->assertSame(1, $first);
        $this->assertSame(0, $second);
        $this->assertTrue(
            PortalNotification::query()
                ->where('user_id', $student->id)
                ->where('type', 'student_talent_voting_closing_soon')
                ->where('related_id', $event->id)
                ->exists()
        );

        (new SendTalentVotingClosingSoonJob(24))->handle($this->notifications);
        $this->assertSame(
            1,
            PortalNotification::query()
                ->where('type', 'student_talent_voting_closing_soon')
                ->where('related_id', $event->id)
                ->where('user_id', $student->id)
                ->count()
        );
    }

    public function test_roster_imported_notifies_super_admins_only(): void
    {
        $super = User::factory()->superAdmin()->create();
        $admin = User::factory()->admin()->create();

        $this->notifications->rosterImported(12, $super);

        $this->assertTrue(
            PortalNotification::query()
                ->where('user_id', $super->id)
                ->where('type', 'admin_roster_imported')
                ->exists()
        );
        $this->assertFalse(
            PortalNotification::query()
                ->where('user_id', $admin->id)
                ->where('type', 'admin_roster_imported')
                ->exists()
        );
    }

    public function test_voting_open_feed_includes_action_url(): void
    {
        $super = User::factory()->superAdmin()->create();
        $student = User::factory()->create();
        $election = Election::factory()->create([
            'title' => 'Deep Link Election',
            'status' => ElectionStatus::Draft,
        ]);

        $this->notifications->votingOpened($election, $super);

        $notification = PortalNotification::query()
            ->where('user_id', $student->id)
            ->where('type', 'student_voting_open')
            ->first();

        $this->assertNotNull($notification);
        $this->assertSame(
            route('student.voting.show', $election),
            $this->notifications->actionUrlFor($notification)
        );
    }

    public function test_published_election_results_notify_faculty_with_results_url(): void
    {
        $admin = User::factory()->admin()->create();
        $faculty = User::factory()->faculty()->create();
        $election = Election::factory()->closed()->create(['title' => 'Faculty Results Election']);

        $this->notifications->resultsPublished($election, $admin);

        $notification = PortalNotification::query()
            ->where('user_id', $faculty->id)
            ->where('type', 'faculty_results_published')
            ->where('related_id', $election->id)
            ->first();

        $this->assertNotNull($notification);
        $this->assertSame(
            route('faculty.results.election.show', $election),
            $this->notifications->actionUrlFor($notification)
        );
    }

    public function test_published_talent_results_notify_faculty_with_results_url(): void
    {
        $admin = User::factory()->admin()->create();
        $faculty = User::factory()->faculty()->create();
        $election = Election::factory()->create();
        $event = TalentEvent::query()->create([
            'election_id' => $election->id,
            'title' => 'Faculty Results Night',
            'slug' => 'faculty-results-night',
            'event_date' => now()->subDay(),
            'status' => TalentEventStatus::ResultsPublished,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'voting_starts_at' => now()->subDays(2),
            'voting_ends_at' => now()->subHour(),
            'results_published_at' => now(),
            'published_to_students' => true,
            'created_by' => $admin->id,
        ]);

        $this->notifications->talentResultsPublished($event, $admin);

        $notification = PortalNotification::query()
            ->where('user_id', $faculty->id)
            ->where('type', 'faculty_talent_results_published')
            ->where('related_id', $event->id)
            ->first();

        $this->assertNotNull($notification);
        $this->assertSame(
            route('faculty.results.talent.show', $event),
            $this->notifications->actionUrlFor($notification)
        );
    }

    public function test_voting_reminder_dedupes_within_twelve_hours(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();
        $election = Election::factory()->active()->create();

        $this->notifications->sendVotingReminder($student, $admin, $election);
        $this->notifications->sendVotingReminder($student, $admin, $election);

        $this->assertSame(
            1,
            PortalNotification::query()
                ->where('user_id', $student->id)
                ->where('type', 'student_voting_reminder')
                ->where('related_id', $election->id)
                ->count()
        );
    }

    public function test_open_on_already_active_election_is_rejected(): void
    {
        $super = User::factory()->superAdmin()->create();
        $election = Election::factory()->active()->create(['is_paused' => false]);

        try {
            app(ElectionLifecycleService::class)->open($election, $super);
            $this->fail('Expected an HttpException for an already-open election.');
        } catch (HttpException $exception) {
            $this->assertSame('This election is already open.', $exception->getMessage());
        }

        $this->assertSame(
            0,
            PortalNotification::query()
                ->where('type', 'student_voting_open')
                ->count()
        );
    }

    public function test_scheduled_election_command_opens_due_drafts(): void
    {
        $super = User::factory()->superAdmin()->create();
        $student = User::factory()->create();
        $election = Election::factory()->create([
            'title' => 'Scheduled Open Election',
            'status' => ElectionStatus::Draft,
            'scheduled_open_at' => now()->subMinute(),
            'created_by' => $super->id,
        ]);

        Artisan::call('portal:process-scheduled-elections');

        $this->assertSame(ElectionStatus::Active, $election->fresh()->status);
        $this->assertTrue(
            PortalNotification::query()
                ->where('user_id', $student->id)
                ->where('type', 'student_voting_open')
                ->where('related_id', $election->id)
                ->exists()
        );
    }

    public function test_scheduled_election_command_closes_when_voting_end_elapsed(): void
    {
        User::factory()->superAdmin()->create();
        $election = Election::factory()->active()->create([
            'title' => 'Should Close',
            'voting_ends_at' => now()->subMinute(),
            'scheduled_close_at' => null,
        ]);

        Artisan::call('portal:process-scheduled-elections');

        $this->assertSame(ElectionStatus::Closed, $election->fresh()->status);
    }

    public function test_prune_notifications_removes_old_read_rows(): void
    {
        $student = User::factory()->create();
        $old = PortalNotification::query()->create([
            'title' => 'Old',
            'message' => 'Old message',
            'type' => 'info',
            'user_id' => $student->id,
            'recipient_role' => 'student',
            'read_at' => now()->subDays(100),
        ]);
        $old->forceFill([
            'created_at' => now()->subDays(100),
            'updated_at' => now()->subDays(100),
        ])->saveQuietly();

        $recent = PortalNotification::query()->create([
            'title' => 'Recent',
            'message' => 'Recent message',
            'type' => 'info',
            'user_id' => $student->id,
            'recipient_role' => 'student',
            'read_at' => now(),
        ]);

        Artisan::call('portal:prune-notifications', ['--days' => 90]);

        $this->assertDatabaseMissing('portal_notifications', ['id' => $old->id]);
        $this->assertDatabaseHas('portal_notifications', ['id' => $recent->id]);
    }

    public function test_announcement_send_email_queues_mail(): void
    {
        Mail::fake();

        $admin = User::factory()->admin()->create();
        $student = User::factory()->create(['email' => 'student@example.com']);

        $announcement = Announcement::query()->create([
            'title' => 'Email Announcement',
            'slug' => 'email-announcement-'.Str::random(6),
            'summary' => 'Summary',
            'body' => 'Body',
            'category' => 'general',
            'priority' => 'normal',
            'target_audiences' => ['students'],
            'status' => 'published',
            'is_published' => true,
            'published_at' => now()->subMinute(),
            'notify_in_app' => true,
            'send_email' => true,
            'created_by' => $admin->id,
        ]);

        $sent = app(AnnouncementService::class)->dispatchNotificationsIfNeeded($announcement, $admin, true);

        $this->assertGreaterThan(0, $sent);
        Mail::assertQueued(AnnouncementPublishedMail::class);
        $this->assertTrue(
            PortalNotification::query()
                ->where('user_id', $student->id)
                ->where('type', 'student_announcement')
                ->exists()
        );
    }
}
