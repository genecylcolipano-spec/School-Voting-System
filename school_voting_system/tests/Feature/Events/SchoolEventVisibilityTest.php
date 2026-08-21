<?php

namespace Tests\Feature\Events;

use App\Enums\EventStatus;
use App\Models\Announcement;
use App\Models\Event;
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
            ->assertDontSee('Cancelled Fair');

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

    public function test_cancelled_create_does_not_generate_an_announcement(): void
    {
        $admin = User::factory()->admin()->create();

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
