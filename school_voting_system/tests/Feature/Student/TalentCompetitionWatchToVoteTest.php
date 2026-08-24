<?php

namespace Tests\Feature\Student;

use App\Enums\TalentEventStatus;
use App\Enums\TalentVotingMethod;
use App\Models\Election;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\TalentEventEntryView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TalentCompetitionWatchToVoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_vote_button_is_locked_until_the_student_opens_watch_performance(): void
    {
        $student = User::factory()->create();
        $event = $this->makeOpenCompetition();
        $this->makeEntry($event, 'Genecyl Colipano', withVideo: true);

        $this->actingAs($student)
            ->get(route('student.talent-voting.show', $event))
            ->assertOk()
            ->assertSee('Watch Performance')
            ->assertSee('Watch the performance first')
            ->assertViewHas('watchedEntryIds', []);
    }

    public function test_opening_watch_performance_records_the_student_and_unlocks_later_votes(): void
    {
        $student = User::factory()->create();
        $event = $this->makeOpenCompetition();
        $entry = $this->makeEntry($event, 'Genecyl Colipano', withVideo: true);

        $this->actingAs($student)
            ->postJson(route('student.talent-voting.view', $entry))
            ->assertOk()
            ->assertJson([
                'watched' => true,
                'entry_id' => $entry->id,
            ]);

        $this->assertTrue(
            TalentEventEntryView::query()
                ->where('user_id', $student->id)
                ->where('talent_event_entry_id', $entry->id)
                ->exists()
        );

        $this->actingAs($student)
            ->get(route('student.talent-voting.show', $event))
            ->assertOk()
            ->assertViewHas('watchedEntryIds', [$entry->id]);

        $this->actingAs($student)
            ->post(route('student.talent-voting.vote', $entry))
            ->assertRedirect(route('student.talent-voting.show', $event))
            ->assertSessionHas('success');
    }

    public function test_vote_is_rejected_if_the_student_has_not_watched_a_video_entry(): void
    {
        $student = User::factory()->create();
        $event = $this->makeOpenCompetition();
        $entry = $this->makeEntry($event, 'Genecyl Colipano', withVideo: true);

        $this->actingAs($student)
            ->from(route('student.talent-voting.show', $event))
            ->post(route('student.talent-voting.vote', $entry))
            ->assertRedirect(route('student.talent-voting.show', $event))
            ->assertSessionHas('error', 'Watch the performance before voting for this entry.');
    }

    public function test_entries_without_video_can_be_voted_without_watching(): void
    {
        $student = User::factory()->create();
        $event = $this->makeOpenCompetition();
        $entry = $this->makeEntry($event, 'No Video Act', withVideo: false);

        $this->actingAs($student)
            ->post(route('student.talent-voting.vote', $entry))
            ->assertRedirect(route('student.talent-voting.show', $event))
            ->assertSessionHas('success');
    }

    protected function makeOpenCompetition(): TalentEvent
    {
        $election = Election::factory()->create();

        return TalentEvent::query()->create([
            'election_id' => $election->id,
            'title' => 'Watch Gate Showcase',
            'slug' => 'watch-gate-showcase-'.uniqid(),
            'event_date' => now()->addDay(),
            'venue' => 'Auditorium',
            'status' => TalentEventStatus::VotingOpen,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'registration_starts_at' => now()->subDays(5),
            'registration_ends_at' => now()->subDay(),
            'voting_starts_at' => now()->subHour(),
            'voting_ends_at' => now()->addDay(),
            'published_to_students' => true,
            'created_by' => User::factory()->admin()->create()->id,
        ]);
    }

    protected function makeEntry(TalentEvent $event, string $name, bool $withVideo): TalentEventEntry
    {
        return TalentEventEntry::query()->create([
            'talent_event_id' => $event->id,
            'display_name' => $name,
            'performance_title' => $name.' Act',
            'status' => TalentEventEntry::STATUS_APPROVED,
            'source' => TalentEventEntry::SOURCE_ADMIN,
            'video_url' => $withVideo ? 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' : null,
        ]);
    }
}
