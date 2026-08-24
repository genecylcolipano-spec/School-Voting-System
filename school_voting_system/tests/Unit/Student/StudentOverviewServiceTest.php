<?php

namespace Tests\Unit\Student;

use App\Models\Election;
use App\Models\Partylist;
use App\Models\User;
use App\Models\Vote;
use App\Services\Student\StudentOverviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesElectionBallotFixtures;
use Tests\TestCase;

class StudentOverviewServiceTest extends TestCase
{
    use CreatesElectionBallotFixtures;
    use RefreshDatabase;

    public function test_open_election_uses_the_same_vote_url_for_hero_and_overview_card(): void
    {
        $student = User::factory()->create();
        $fixture = $this->createElectionBallot(1);
        Partylist::factory()->create(['name' => 'Unity Party']);

        $overview = app(StudentOverviewService::class)->forStudent($student);
        $elections = collect($overview['cards'])->firstWhere('key', 'elections');

        $this->assertTrue($overview['can_vote_now']);
        $this->assertNull($overview['vote_now_hint']);
        $this->assertSame(route('student.voting.show', $fixture['election']), $overview['vote_now_url']);
        $this->assertSame($overview['vote_now_url'], $elections['href']);
        $this->assertSame('Vote now →', $elections['action']);
        $this->assertSame(1, $elections['count']);
        $this->assertSame('1 open now', $elections['status']);
        $this->assertTrue($elections['enabled']);
    }

    public function test_completed_ballot_keeps_open_count_but_stops_vote_now(): void
    {
        $student = User::factory()->create();
        $fixture = $this->createElectionBallot(2);

        Vote::withoutEvents(function () use ($student, $fixture) {
            foreach ($fixture['categories'] as $index => $category) {
                Vote::query()->create([
                    'user_id' => $student->id,
                    'election_id' => $fixture['election']->id,
                    'election_category_id' => $category->id,
                    'candidate_id' => $fixture['candidates'][$index]->id,
                    'voted_at' => now(),
                ]);
            }
        });

        $overview = app(StudentOverviewService::class)->forStudent($student);
        $elections = collect($overview['cards'])->firstWhere('key', 'elections');

        $this->assertFalse($overview['can_vote_now']);
        $this->assertSame('You have already voted.', $overview['vote_now_hint']);
        $this->assertSame(route('student.voting.index'), $overview['vote_now_url']);
        $this->assertSame($overview['vote_now_url'], $elections['href']);
        $this->assertSame('You have voted', $elections['status']);
        $this->assertSame('View voting →', $elections['action']);
        $this->assertSame(1, $elections['count']);
    }

    public function test_overview_swaps_announcements_for_campaigns_and_keeps_every_card_clickable(): void
    {
        $student = User::factory()->create();
        Election::factory()->draft()->create();
        Partylist::factory()->create();
        Partylist::factory()->draft()->create();

        $overview = app(StudentOverviewService::class)->forStudent($student);
        $cards = collect($overview['cards']);

        $this->assertSame(
            ['elections', 'events', 'talent', 'fundraising', 'campaigns'],
            $cards->pluck('key')->all(),
        );
        $this->assertFalse($cards->contains(fn (array $card) => $card['key'] === 'announcements'));
        $this->assertTrue($cards->every(fn (array $card) => $card['enabled'] === true && filled($card['href'])));
        $this->assertSame(1, $cards->firstWhere('key', 'campaigns')['count']);
        $this->assertSame(route('student.campaigns.index'), $cards->firstWhere('key', 'campaigns')['href']);
        $this->assertStringNotContainsStringIgnoringCase('campaign', $cards->firstWhere('key', 'fundraising')['status']);
    }
}
