<?php

namespace Tests\Feature\Campaign;

use App\Enums\CampaignStatus;
use App\Enums\ElectionStatus;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionCategory;
use App\Models\Partylist;
use App\Models\User;
use App\Models\Vote;
use App\Services\Admin\AdminAnalyticsService;
use App\Services\Admin\ElectionSetupService;
use App\Services\Campaign\StudentCampaignService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesElectionBallotFixtures;
use Tests\Support\TestImageFactory;
use Tests\TestCase;

class CampaignModuleTest extends TestCase
{
    use CreatesElectionBallotFixtures;
    use RefreshDatabase;

    public function test_campaign_can_be_created_without_an_election(): void
    {
        $campaign = Partylist::factory()->create(['election_id' => null]);

        $this->assertNull($campaign->election_id);
        $this->assertSame(CampaignStatus::Active, $campaign->fresh()->status);
        $this->assertDatabaseHas('partylists', ['id' => $campaign->id, 'election_id' => null]);
    }

    public function test_campaign_can_be_reused_across_multiple_elections(): void
    {
        $campaign = Partylist::factory()->create();
        $electionA = Election::factory()->create();
        $electionB = Election::factory()->create();

        $campaign->elections()->sync([$electionA->id, $electionB->id]);

        $this->assertEqualsCanonicalizing(
            [$electionA->id, $electionB->id],
            $campaign->fresh()->elections->pluck('id')->all(),
        );
        $this->assertTrue($electionA->fresh()->partylists->contains($campaign));
        $this->assertTrue($electionB->fresh()->partylists->contains($campaign));
        $this->assertDatabaseCount('election_partylist', 2);
    }

    public function test_election_setup_saves_candidate_partylist_fk_and_label(): void
    {
        $campaign = Partylist::factory()->create(['name' => 'Achievers Party']);
        $election = Election::factory()->create();

        app(ElectionSetupService::class)->syncOnCreate($election, [
            'partylists' => [$campaign->id],
            'positions' => [['name' => 'President']],
            'candidates' => [
                ['display_name' => 'Alice Cruz', 'position_index' => 0, 'partylist_id' => $campaign->id],
            ],
        ]);

        $candidate = Candidate::query()->where('election_id', $election->id)->first();

        $this->assertNotNull($candidate);
        $this->assertSame($campaign->id, $candidate->partylist_id);
        $this->assertSame('Achievers Party', $candidate->party_or_group);
        $this->assertTrue($election->partylists->contains($campaign));
    }

    public function test_campaign_performance_aggregates_by_partylist_fk(): void
    {
        $election = Election::factory()->active()->create();
        $category = ElectionCategory::factory()->create(['election_id' => $election->id]);

        $campaignX = Partylist::factory()->create();
        $campaignY = Partylist::factory()->create();
        $election->partylists()->sync([$campaignX->id, $campaignY->id]);

        $candidateX = Candidate::factory()->create([
            'election_id' => $election->id,
            'election_category_id' => $category->id,
            'partylist_id' => $campaignX->id,
        ]);
        $candidateY = Candidate::factory()->create([
            'election_id' => $election->id,
            'election_category_id' => $category->id,
            'partylist_id' => $campaignY->id,
        ]);

        Vote::castBallot(User::factory()->create(), $candidateX);
        Vote::castBallot(User::factory()->create(), $candidateX);
        Vote::castBallot(User::factory()->create(), $candidateY);

        $admin = User::factory()->superAdmin()->create();
        $performance = collect(app(AdminAnalyticsService::class)->campaignPerformance($admin))
            ->keyBy('partylist_id');

        $this->assertSame(2, $performance[$campaignX->id]['total_votes']);
        $this->assertSame(1, $performance[$campaignX->id]['winning_candidates']);
        $this->assertContains($category->name, $performance[$campaignX->id]['winning_positions']);
        $this->assertSame(1, $performance[$campaignY->id]['total_votes']);
        $this->assertSame(0, $performance[$campaignY->id]['winning_candidates']);
    }

    public function test_button_state_when_no_election(): void
    {
        $campaign = Partylist::factory()->create();

        $state = $this->buttonStateFor($campaign);

        $this->assertSame('no_election', $state['state']);
        $this->assertFalse($state['enabled']);
        $this->assertSame('No Election Available', $state['label']);
    }

    public function test_button_state_when_election_draft(): void
    {
        $campaign = Partylist::factory()->create();
        $election = Election::factory()->create(['status' => ElectionStatus::Draft]);
        $campaign->elections()->sync([$election->id]);

        $state = $this->buttonStateFor($campaign);

        $this->assertSame('draft', $state['state']);
        $this->assertSame('Election Not Ready', $state['label']);
    }

    public function test_button_state_before_voting_opens(): void
    {
        $campaign = Partylist::factory()->create();
        $election = Election::factory()->create([
            'status' => ElectionStatus::Active,
            'voting_starts_at' => now()->addDay(),
            'voting_ends_at' => now()->addDays(2),
        ]);
        $campaign->elections()->sync([$election->id]);

        $state = $this->buttonStateFor($campaign);

        $this->assertSame('not_started', $state['state']);
        $this->assertSame('Please wait until voting opens', $state['label']);
    }

    public function test_button_state_vote_now_when_active_and_not_voted(): void
    {
        ['election' => $election] = $this->createElectionBallot(1);
        $campaign = Partylist::factory()->create();
        $campaign->elections()->sync([$election->id]);

        $state = $this->buttonStateFor($campaign, User::factory()->create());

        $this->assertSame('open', $state['state']);
        $this->assertTrue($state['enabled']);
        $this->assertSame('Vote Now', $state['label']);
        $this->assertNotNull($state['url']);
    }

    public function test_button_state_already_voted(): void
    {
        ['election' => $election, 'candidates' => $candidates] = $this->createElectionBallot(1);
        $student = User::factory()->create();
        Vote::castBallot($student, $candidates[0]);

        $campaign = Partylist::factory()->create();
        $campaign->elections()->sync([$election->id]);

        $state = $this->buttonStateFor($campaign, $student);

        $this->assertSame('voted', $state['state']);
        $this->assertSame('You Have Already Voted', $state['label']);
        $this->assertFalse($state['enabled']);
    }

    public function test_button_state_results_under_review(): void
    {
        $campaign = Partylist::factory()->create();
        $election = Election::factory()->closed()->create(['public_results_published' => false]);
        $campaign->elections()->sync([$election->id]);

        $state = $this->buttonStateFor($campaign);

        $this->assertSame('under_review', $state['state']);
        $this->assertSame('Results Under Review', $state['label']);
    }

    public function test_button_state_view_official_results(): void
    {
        $campaign = Partylist::factory()->create();
        $election = Election::factory()->closed()->create(['public_results_published' => true]);
        $campaign->elections()->sync([$election->id]);

        $state = $this->buttonStateFor($campaign, User::factory()->create());

        $this->assertSame('results_published', $state['state']);
        $this->assertTrue($state['enabled']);
        $this->assertSame('View Official Results', $state['label']);
        $this->assertNotNull($state['url']);
    }

    public function test_campaign_candidates_are_scoped_to_relevant_election_and_campaign(): void
    {
        $campaign = Partylist::factory()->create();
        $otherCampaign = Partylist::factory()->create();

        $activeElection = Election::factory()->active()->create();
        $pastElection = Election::factory()->closed()->create();
        $campaign->elections()->sync([$activeElection->id, $pastElection->id]);

        $activeCategory = ElectionCategory::factory()->create([
            'election_id' => $activeElection->id,
            'name' => 'President',
            'sort_order' => 1,
        ]);
        $pastCategory = ElectionCategory::factory()->create([
            'election_id' => $pastElection->id,
            'name' => 'Vice President',
            'sort_order' => 1,
        ]);

        $expected = Candidate::factory()->create([
            'election_id' => $activeElection->id,
            'election_category_id' => $activeCategory->id,
            'partylist_id' => $campaign->id,
            'display_name' => 'Alice Active',
        ]);
        Candidate::factory()->create([
            'election_id' => $pastElection->id,
            'election_category_id' => $pastCategory->id,
            'partylist_id' => $campaign->id,
            'display_name' => 'Bob Past Election',
        ]);
        Candidate::factory()->create([
            'election_id' => $activeElection->id,
            'election_category_id' => $activeCategory->id,
            'partylist_id' => $otherCampaign->id,
            'display_name' => 'Carol Other Campaign',
        ]);

        $service = app(StudentCampaignService::class);
        $relevantElection = $service->relevantElection($campaign);
        $candidates = $service->candidatesFor($campaign, $relevantElection);

        $this->assertSame($activeElection->id, $relevantElection->id);
        $this->assertCount(1, $candidates);
        $this->assertSame($expected->id, $candidates->first()->id);
        $this->assertSame('President', $candidates->first()->category->name);
    }

    public function test_student_campaign_page_shows_only_scoped_candidates(): void
    {
        $student = User::factory()->create();
        $campaign = Partylist::factory()->create();
        $election = Election::factory()->active()->create();
        $campaign->elections()->sync([$election->id]);

        $category = ElectionCategory::factory()->create([
            'election_id' => $election->id,
            'name' => 'President',
        ]);

        Candidate::factory()->create([
            'election_id' => $election->id,
            'election_category_id' => $category->id,
            'partylist_id' => $campaign->id,
            'display_name' => 'Included Candidate',
        ]);
        Candidate::factory()->create([
            'election_id' => $election->id,
            'election_category_id' => $category->id,
            'partylist_id' => Partylist::factory()->create()->id,
            'display_name' => 'Excluded Candidate',
        ]);

        $response = $this->actingAs($student)
            ->get(route('student.campaigns.show', $campaign));

        $response->assertOk();
        $response->assertSee('Included Candidate');
        $response->assertDontSee('Excluded Candidate');
    }

    public function test_landscape_banner_is_detected_for_adaptive_display(): void
    {
        Storage::fake('public');

        $path = TestImageFactory::storeLandscapeBanner();
        $campaign = Partylist::factory()->create(['banner_path' => $path]);

        $this->assertTrue($campaign->hasLandscapeBanner());
        $this->assertFalse($campaign->isPortraitBanner());
        $this->assertNotNull($campaign->bannerUrl());
    }

    public function test_portrait_banner_is_detected_for_adaptive_display(): void
    {
        Storage::fake('public');

        $path = TestImageFactory::storePortraitBanner();
        $campaign = Partylist::factory()->create(['banner_path' => $path]);

        $this->assertFalse($campaign->hasLandscapeBanner());
        $this->assertTrue($campaign->isPortraitBanner());
        $this->assertNotNull($campaign->bannerUrl());
    }

    public function test_portrait_campaign_banner_upload_is_accepted(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.campaigns.store'), [
            'name' => 'Poster Party',
            'status' => CampaignStatus::Active->value,
            'banner' => TestImageFactory::portraitUploadedFile(),
        ]);

        $response->assertRedirect(route('admin.campaigns.index'));
        $this->assertDatabaseHas('partylists', ['name' => 'Poster Party']);
    }

    protected function buttonStateFor(Partylist $campaign, ?User $student = null): array
    {
        return app(StudentCampaignService::class)->buttonStateFor($campaign, $student);
    }
}
