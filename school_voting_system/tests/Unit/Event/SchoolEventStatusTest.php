<?php

namespace Tests\Unit\Event;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolEventStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_past_scheduled_event_displays_as_completed(): void
    {
        $event = $this->makeEvent([
            'event_date' => now()->subDay(),
            'status' => EventStatus::Scheduled,
        ]);

        $this->assertSame(EventStatus::Completed, $event->displayStatus());
        $this->assertSame('Completed', $event->displayStatusLabel());
        $this->assertTrue($event->isVisibleToCampus());
    }

    public function test_started_event_today_displays_as_ongoing(): void
    {
        $this->travelTo(now()->setTime(10, 0));

        $event = $this->makeEvent([
            'event_date' => now()->subHour(),
            'status' => EventStatus::Scheduled,
        ]);

        $this->assertSame(EventStatus::Ongoing, $event->displayStatus());
        $this->assertSame('Ongoing', $event->displayStatusLabel());
        $this->assertSame('ongoing', $event->campusStatusKey());
    }

    public function test_later_today_event_stays_scheduled(): void
    {
        $this->travelTo(now()->setTime(10, 0));

        $event = $this->makeEvent([
            'event_date' => now()->setTime(18, 30),
            'status' => EventStatus::Scheduled,
        ]);

        $this->assertSame(EventStatus::Scheduled, $event->displayStatus());
        $this->assertSame('upcoming', $event->campusStatusKey());
    }

    public function test_cancelled_event_stays_cancelled_and_hidden_from_campus(): void
    {
        $event = $this->makeEvent([
            'event_date' => now()->addDay(),
            'status' => EventStatus::Cancelled,
        ]);

        $this->assertSame(EventStatus::Cancelled, $event->displayStatus());
        $this->assertFalse($event->isVisibleToCampus());
        $this->assertTrue(Event::query()->visibleToCampus()->whereKey($event)->doesntExist());
    }

    public function test_mark_overdue_persists_completed_status_for_previous_days(): void
    {
        $event = $this->makeEvent([
            'event_date' => now()->subDay(),
            'status' => EventStatus::Scheduled,
        ]);

        $updated = Event::markOverdueAsCompleted();

        $this->assertSame(1, $updated);
        $this->assertSame(EventStatus::Completed, $event->fresh()->status);
    }

    public function test_mark_overdue_persists_ongoing_status_after_start_today(): void
    {
        $this->travelTo(now()->setTime(10, 0));

        $event = $this->makeEvent([
            'event_date' => now()->subHour(),
            'status' => EventStatus::Scheduled,
        ]);

        $updated = Event::markOverdueAsCompleted();

        $this->assertSame(1, $updated);
        $this->assertSame(EventStatus::Ongoing, $event->fresh()->status);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeEvent(array $overrides = []): Event
    {
        return Event::query()->create(array_merge([
            'title' => 'Campus Orientation',
            'slug' => 'campus-orientation',
            'event_date' => now()->addDays(3),
            'venue' => 'Online',
            'status' => EventStatus::Scheduled,
            'created_by' => User::factory()->admin()->create()->id,
        ], $overrides));
    }
}
