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

    public function test_mark_overdue_persists_completed_status(): void
    {
        $event = $this->makeEvent([
            'event_date' => now()->subHour(),
            'status' => EventStatus::Scheduled,
        ]);

        $updated = Event::markOverdueAsCompleted();

        $this->assertSame(1, $updated);
        $this->assertSame(EventStatus::Completed, $event->fresh()->status);
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
