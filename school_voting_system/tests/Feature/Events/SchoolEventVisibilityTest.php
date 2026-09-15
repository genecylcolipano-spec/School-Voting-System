<?php

namespace Tests\Feature\Events;

use App\Enums\EventStatus;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\PortalNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolEventVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_do_not_see_cancelled_events(): void
    {
        $student = User::factory()->create();
        $visible = $this->makeEvent([
            'title' => 'Foundation Day',
            'slug' => 'foundation-day',
            'status' => EventStatus::Scheduled,
        ]);
        $this->makeEvent([
            'title' => 'Cancelled Fair',
            'slug' => 'cancelled-fair',
            'status' => EventStatus::Cancelled,
        ]);

        $this->actingAs($student)
            ->get(route('student.events.index'))
            ->assertOk()
            ->assertSee('Foundation Day')
            ->assertDontSee('Cancelled Fair')
            ->assertSee('Welcome back')
            ->assertSee('Browse upcoming and past school events')
            ->assertDontSee('Browse school events and announcements');

        $this->actingAs($student)
            ->get(route('student.events.show', $visible))
            ->assertOk();

        $this->actingAs($student)
            ->get(route('student.events.show', 'cancelled-fair'))
            ->assertNotFound();
    }

    public function test_faculty_do_not_see_cancelled_events(): void
    {
        $faculty = User::factory()->faculty()->create();
        $this->makeEvent([
            'title' => 'Faculty Assembly',
            'slug' => 'faculty-assembly',
            'status' => EventStatus::Scheduled,
        ]);
        $this->makeEvent([
            'title' => 'Cancelled Recollection',
            'slug' => 'cancelled-recollection',
            'status' => EventStatus::Cancelled,
        ]);

        $this->actingAs($faculty)
            ->get(route('faculty.events.index'))
            ->assertOk()
            ->assertSee('Faculty Assembly')
            ->assertDontSee('Cancelled Recollection');

        $this->actingAs($faculty)
            ->get(route('faculty.events.show', 'cancelled-recollection'))
            ->assertNotFound();
    }

    public function test_faculty_event_pages_show_time_status_badge_and_filters(): void
    {
        $faculty = User::factory()->faculty()->create();
        $startsAt = now()->addDays(2)->setTime(14, 30);
        $upcoming = $this->makeEvent([
            'title' => 'Faculty Science Exhibit',
            'slug' => 'faculty-science-exhibit',
            'event_date' => $startsAt,
            'venue' => 'AVR 2',
            'status' => EventStatus::Scheduled,
        ]);
        $this->makeEvent([
            'title' => 'Past Faculty Intramurals',
            'slug' => 'past-faculty-intramurals',
            'event_date' => now()->subDay(),
            'status' => EventStatus::Completed,
        ]);

        $expectedSchedule = $startsAt->format('M d, Y · g:i A');

        $this->actingAs($faculty)
            ->get(route('faculty.events.index'))
            ->assertOk()
            ->assertSee('All')
            ->assertSee('Upcoming')
            ->assertSee('Faculty Science Exhibit')
            ->assertSee('Past Faculty Intramurals')
            ->assertSee($expectedSchedule)
            ->assertSee('Upcoming')
            ->assertSee('Completed')
            ->assertDontSee('Show all events');

        $this->actingAs($faculty)
            ->get(route('faculty.events.index', ['filter' => 'upcoming']))
            ->assertOk()
            ->assertSee('Faculty Science Exhibit')
            ->assertDontSee('Past Faculty Intramurals');

        $this->actingAs($faculty)
            ->get(route('faculty.events.show', $upcoming))
            ->assertOk()
            ->assertSee('View only')
            ->assertSee('Faculty Science Exhibit')
            ->assertSee($expectedSchedule)
            ->assertSee('AVR 2')
            ->assertSee('Upcoming');
    }

    public function test_past_scheduled_event_is_marked_completed_on_student_list(): void
    {
        $student = User::factory()->create();
        $event = $this->makeEvent([
            'title' => 'Yesterday Intramurals',
            'slug' => 'yesterday-intramurals',
            'event_date' => now()->subDay(),
            'status' => EventStatus::Scheduled,
        ]);

        $this->actingAs($student)
            ->get(route('student.events.index'))
            ->assertOk()
            ->assertSee('Yesterday Intramurals')
            ->assertSee('Completed');

        $this->assertSame(EventStatus::Completed, $event->fresh()->status);
    }

    public function test_started_scheduled_event_is_marked_ongoing_on_student_list(): void
    {
        $this->travelTo(now()->setTime(10, 0));

        $student = User::factory()->create();
        $event = $this->makeEvent([
            'title' => 'Morning Flag Ceremony',
            'slug' => 'morning-flag-ceremony',
            'event_date' => now()->subHour(),
            'status' => EventStatus::Scheduled,
        ]);

        $this->actingAs($student)
            ->get(route('student.events.index'))
            ->assertOk()
            ->assertSee('Morning Flag Ceremony')
            ->assertSee('Ongoing');

        $this->assertSame(EventStatus::Ongoing, $event->fresh()->status);
    }

    public function test_student_event_pages_show_time_and_status_badge(): void
    {
        $student = User::factory()->create();
        $startsAt = now()->addDays(2)->setTime(14, 30);
        $event = $this->makeEvent([
            'title' => 'Science Exhibit',
            'slug' => 'science-exhibit',
            'event_date' => $startsAt,
            'venue' => 'AVR 2',
            'status' => EventStatus::Scheduled,
        ]);

        $expectedSchedule = $startsAt->format('M d, Y · g:i A');

        $this->actingAs($student)
            ->get(route('student.events.index'))
            ->assertOk()
            ->assertSee('Science Exhibit')
            ->assertSee($expectedSchedule)
            ->assertSee('Upcoming')
            ->assertSee('View details');

        $this->actingAs($student)
            ->get(route('student.events.show', $event))
            ->assertOk()
            ->assertSee('Welcome back')
            ->assertSee('Science Exhibit')
            ->assertSee($expectedSchedule)
            ->assertSee('AVR 2')
            ->assertSee('Upcoming');
    }

    public function test_updating_event_keeps_slug_when_title_is_unchanged(): void
    {
        $admin = User::factory()->admin()->create();
        $event = $this->makeEvent([
            'title' => 'Sports Fest',
            'slug' => 'sports-fest',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.events.update', $event), [
                'title' => 'Sports Fest',
                'description' => 'Updated details',
                'event_date' => now()->addDays(4)->format('Y-m-d\TH:i'),
                'venue' => 'Main Gym',
                'status' => EventStatus::Scheduled->value,
            ])
            ->assertRedirect(route('admin.events.index'));

        $this->assertSame('sports-fest', $event->fresh()->slug);
    }

    public function test_regular_admin_cannot_update_another_admins_school_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = $this->makeEvent([
            'title' => 'Other Admin Event',
            'slug' => 'other-admin-event',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.events.update', $event), [
                'title' => 'Hijacked Title',
                'description' => 'Nope',
                'event_date' => now()->addDays(4)->format('Y-m-d\TH:i'),
                'venue' => 'Main Gym',
                'status' => EventStatus::Scheduled->value,
            ])
            ->assertForbidden();

        $this->assertSame('Other Admin Event', $event->fresh()->title);
    }

    public function test_cancelled_create_does_not_generate_an_announcement(): void
    {
        $admin = User::factory()->admin()->create();
        $superAdmin = User::factory()->superAdmin()->create();
        $faculty = User::factory()->faculty()->create();
        $student = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.events.store'), [
                'title' => 'Storm Advisory Assembly',
                'event_date' => now()->addDay()->format('Y-m-d\TH:i'),
                'venue' => 'Covered Court',
                'status' => EventStatus::Cancelled->value,
            ])
            ->assertRedirect(route('admin.events.index'));

        $this->assertDatabaseHas('events', [
            'title' => 'Storm Advisory Assembly',
            'status' => EventStatus::Cancelled->value,
        ]);
        $this->assertSame(0, Announcement::query()->count());

        $event = Event::query()->where('title', 'Storm Advisory Assembly')->first();
        $this->assertNotNull($event);

        foreach ([$admin, $superAdmin] as $recipient) {
            $this->assertTrue(
                PortalNotification::query()
                    ->where('user_id', $recipient->id)
                    ->where('type', 'admin_event_cancelled')
                    ->where('related_id', $event->id)
                    ->exists()
            );
        }
        $this->assertFalse(
            PortalNotification::query()
                ->where('user_id', $faculty->id)
                ->where('type', 'faculty_event_published')
                ->exists()
        );
        $this->assertFalse(
            PortalNotification::query()
                ->where('user_id', $student->id)
                ->where('type', 'student_event_reminder')
                ->exists()
        );
    }

    public function test_creating_scheduled_event_notifies_staff_and_campus(): void
    {
        $admin = User::factory()->admin()->create();
        $superAdmin = User::factory()->superAdmin()->create();
        $faculty = User::factory()->faculty()->create();
        $student = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.events.store'), [
                'title' => 'Foundation Day',
                'event_date' => now()->addDays(3)->format('Y-m-d\TH:i'),
                'venue' => 'Covered Court',
                'status' => EventStatus::Scheduled->value,
            ])
            ->assertRedirect(route('admin.events.index'));

        $event = Event::query()->where('title', 'Foundation Day')->first();
        $this->assertNotNull($event);
        $this->assertSame(1, Announcement::query()->count());

        foreach ([$admin, $superAdmin] as $recipient) {
            $this->assertTrue(
                PortalNotification::query()
                    ->where('user_id', $recipient->id)
                    ->where('type', 'admin_event_scheduled')
                    ->where('related_id', $event->id)
                    ->exists()
            );
        }
        $this->assertTrue(
            PortalNotification::query()
                ->where('user_id', $faculty->id)
                ->where('type', 'faculty_event_published')
                ->where('related_id', $event->id)
                ->exists()
        );
        $this->assertTrue(
            PortalNotification::query()
                ->where('user_id', $student->id)
                ->where('type', 'student_event_reminder')
                ->where('related_id', $event->id)
                ->exists()
        );
    }

    public function test_creating_completed_event_notifies_admins_only(): void
    {
        $admin = User::factory()->admin()->create();
        $superAdmin = User::factory()->superAdmin()->create();
        $faculty = User::factory()->faculty()->create();
        $student = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.events.store'), [
                'title' => 'Last Week Recollection',
                'event_date' => now()->subDays(2)->format('Y-m-d\TH:i'),
                'venue' => 'Chapel',
                'status' => EventStatus::Completed->value,
            ])
            ->assertRedirect(route('admin.events.index'));

        $event = Event::query()->where('title', 'Last Week Recollection')->first();
        $this->assertNotNull($event);
        $this->assertSame(0, Announcement::query()->count());

        foreach ([$admin, $superAdmin] as $recipient) {
            $this->assertTrue(
                PortalNotification::query()
                    ->where('user_id', $recipient->id)
                    ->where('type', 'admin_event_completed')
                    ->where('related_id', $event->id)
                    ->exists()
            );
        }
        $this->assertFalse(
            PortalNotification::query()
                ->where('user_id', $faculty->id)
                ->where('type', 'faculty_event_published')
                ->exists()
        );
        $this->assertFalse(
            PortalNotification::query()
                ->where('user_id', $student->id)
                ->where('type', 'student_event_reminder')
                ->exists()
        );
    }

    public function test_creating_ongoing_event_notifies_admins_and_campus_without_announcement(): void
    {
        $this->travelTo(now()->setTime(10, 0));

        $admin = User::factory()->admin()->create();
        $superAdmin = User::factory()->superAdmin()->create();
        $faculty = User::factory()->faculty()->create();
        $student = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.events.store'), [
                'title' => 'Intramurals Opening',
                'event_date' => now()->subHour()->format('Y-m-d\TH:i'),
                'venue' => 'Main Gym',
                'status' => EventStatus::Ongoing->value,
            ])
            ->assertRedirect(route('admin.events.index'));

        $event = Event::query()->where('title', 'Intramurals Opening')->first();
        $this->assertNotNull($event);
        $this->assertSame(EventStatus::Ongoing, $event->status);
        $this->assertSame(0, Announcement::query()->count());

        foreach ([$admin, $superAdmin] as $recipient) {
            $this->assertTrue(
                PortalNotification::query()
                    ->where('user_id', $recipient->id)
                    ->where('type', 'admin_event_ongoing')
                    ->where('related_id', $event->id)
                    ->exists()
            );
        }
        $this->assertTrue(
            PortalNotification::query()
                ->where('user_id', $faculty->id)
                ->where('type', 'faculty_event_published')
                ->where('related_id', $event->id)
                ->exists()
        );
        $this->assertTrue(
            PortalNotification::query()
                ->where('user_id', $student->id)
                ->where('type', 'student_event_reminder')
                ->where('related_id', $event->id)
                ->exists()
        );
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeEvent(array $overrides = []): Event
    {
        return Event::query()->create(array_merge([
            'title' => 'School Event',
            'slug' => 'school-event-'.uniqid(),
            'event_date' => now()->addDays(2),
            'venue' => 'Auditorium',
            'status' => EventStatus::Scheduled,
            'created_by' => User::factory()->admin()->create()->id,
        ], $overrides));
    }
}
