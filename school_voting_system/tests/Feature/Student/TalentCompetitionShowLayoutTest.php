<?php

namespace Tests\Feature\Student;

use App\Enums\TalentEventStatus;
use App\Enums\TalentRegistrationMethod;
use App\Enums\TalentVotingMethod;
use App\Models\Election;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\TalentEventVote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TalentCompetitionShowLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_voting_open_page_puts_participants_before_details_and_drops_duplicates(): void
    {
        $student = User::factory()->create();
        $event = $this->makeCompetition([
            'title' => 'Showcase 2026',
            'slug' => 'showcase-2026-layout',
            'status' => TalentEventStatus::VotingOpen,
            'registration_starts_at' => now()->subDays(5),
            'registration_ends_at' => now()->subDay(),
            'voting_starts_at' => now()->subHour(),
            'voting_ends_at' => now()->addDay(),
        ]);
        $this->makeEntry($event, 'Genecyl Colipano');

        $this->actingAs($student)
            ->get(route('student.talent-voting.show', $event))
            ->assertOk()
            ->assertSeeInOrder([
                'Vote Now',
                'Approved Participants',
                'Voting Ends In',
                'Your Vote',
                'Not voted yet',
                'You may vote for ONE (1) talent entry.',
                'Participants',
                'Genecyl Colipano',
                'Competition details',
                'Need Help?',
            ])
            ->assertDontSee('Competition Rules')
            ->assertDontSee('Reminders')
            ->assertDontSee('Competition Progress')
            ->assertDontSee('Registration Closed')
            ->assertDontSee('Registration Period')
            ->assertDontSee('Total Votes Cast')
            ->assertDontSee('Current Status')
            ->assertDontSee('You haven\'t voted yet.');
    }

    public function test_registration_open_keeps_progress_and_hides_vote_cta(): void
    {
        $student = User::factory()->create();
        $event = $this->makeCompetition([
            'title' => 'Open Registration Night',
            'slug' => 'open-registration-night',
            'status' => TalentEventStatus::Scheduled,
            'registration_method' => TalentRegistrationMethod::Both,
            'registration_starts_at' => now()->subHour(),
            'registration_ends_at' => now()->addDay(),
            'voting_starts_at' => now()->addDays(2),
            'voting_ends_at' => now()->addDays(4),
        ]);

        $this->actingAs($student)
            ->get(route('student.talent-voting.show', $event))
            ->assertOk()
            ->assertSee('Register Now')
            ->assertSee('Competition Progress')
            ->assertSee('Registration Period')
            ->assertSee('Registration Ends In')
            ->assertDontSee('Vote Now')
            ->assertDontSee('Competition Rules');
    }

    public function test_published_results_surface_near_the_top_without_vote_now(): void
    {
        $student = User::factory()->create();
        $event = $this->makeCompetition([
            'title' => 'Finished Showcase',
            'slug' => 'finished-showcase-layout',
            'status' => TalentEventStatus::ResultsPublished,
            'registration_starts_at' => now()->subDays(10),
            'registration_ends_at' => now()->subDays(8),
            'voting_starts_at' => now()->subDays(5),
            'voting_ends_at' => now()->subDay(),
            'results_published_at' => now()->subHour(),
        ]);
        $entry = $this->makeEntry($event, 'Winner Student');
        TalentEventVote::query()->create([
            'talent_event_id' => $event->id,
            'talent_event_entry_id' => $entry->id,
            'user_id' => $student->id,
            'voted_at' => now()->subHours(2),
        ]);

        $this->actingAs($student)
            ->get(route('student.talent-voting.show', $event))
            ->assertOk()
            ->assertSeeInOrder([
                'View Results',
                'Voted for Winner Student',
                'Results Published',
                'View Official Results',
                'Participants',
                'Competition details',
            ])
            ->assertDontSee('Vote Now')
            ->assertDontSee('Competition Progress')
            ->assertDontSee('Competition Rules');
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
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'published_to_students' => true,
            'created_by' => User::factory()->admin()->create()->id,
        ], $overrides));
    }

    protected function makeEntry(TalentEvent $event, string $name): TalentEventEntry
    {
        return TalentEventEntry::query()->create([
            'talent_event_id' => $event->id,
            'display_name' => $name,
            'performance_title' => $name.' Act',
            'status' => TalentEventEntry::STATUS_APPROVED,
            'source' => TalentEventEntry::SOURCE_ADMIN,
        ]);
    }
}
