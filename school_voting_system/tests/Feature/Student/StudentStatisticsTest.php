<?php

namespace Tests\Feature\Student;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesElectionBallotFixtures;
use Tests\TestCase;

class StudentStatisticsTest extends TestCase
{
    use CreatesElectionBallotFixtures;
    use RefreshDatabase;

    public function test_statistics_page_uses_personal_labels(): void
    {
        $student = User::factory()->create();
        $this->createElectionBallot(1);

        $this->actingAs($student)
            ->get(route('student.statistics'))
            ->assertOk()
            ->assertSee('Your Statistics')
            ->assertSee('Positions voted')
            ->assertSee('Drives supported')
            ->assertSee('Most Active Month')
            ->assertSee('Supporter Level')
            ->assertSee('Not yet donated')
            ->assertDontSee('Campaigns Supported')
            ->assertDontSee('Most Active Semester')
            ->assertDontSee('Certificates Earned')
            ->assertDontSee('Completed Votes');
    }
}
