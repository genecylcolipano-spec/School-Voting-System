<?php

namespace Tests\Feature\Student;

use App\Models\Partylist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesElectionBallotFixtures;
use Tests\TestCase;

class StudentDashboardOverviewTest extends TestCase
{
    use CreatesElectionBallotFixtures;
    use RefreshDatabase;

    public function test_dashboard_shows_personalized_vote_now_and_campaign_card(): void
    {
        $student = User::factory()->create(['name' => 'Campus Student']);
        $fixture = $this->createElectionBallot(1);
        Partylist::factory()->create(['name' => 'Progress Alliance']);

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Welcome back, Campus Student')
            ->assertSee('Student Dashboard')
            ->assertSee('Student Overview')
            ->assertSee('Election Campaigns')
            ->assertSee('View campaigns →')
            ->assertSee('Latest Announcements')
            ->assertSee('Vote now →')
            ->assertSee(route('student.voting.show', $fixture['election']), false)
            ->assertDontSee('Read now →')
            ->assertDontSee('Recent Notifications');
    }
}
