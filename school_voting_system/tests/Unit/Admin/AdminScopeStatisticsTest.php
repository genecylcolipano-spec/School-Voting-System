<?php

namespace Tests\Unit\Admin;

use App\Enums\ElectionStatus;
use App\Enums\StudentStatus;
use App\Enums\UserRole;
use App\Models\AdminAssignment;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionCategory;
use App\Models\User;
use App\Models\Vote;
use App\Services\Admin\AdminScopeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminScopeStatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_scoped_students_include_unassigned_grade_and_section(): void
    {
        $admin = User::factory()->admin()->create();
        $election = Election::factory()->create([
            'status' => ElectionStatus::Active,
            'created_by' => $admin->id,
        ]);

        AdminAssignment::query()->create([
            'user_id' => $admin->id,
            'election_id' => $election->id,
            'grade_levels' => ['10', '11'],
            'sections' => ['A', 'B'],
            'assigned_by' => $admin->id,
        ]);

        $unassigned = User::factory()->create([
            'role' => UserRole::Student,
            'is_active' => true,
            'student_status' => StudentStatus::Enrolled,
            'grade_level' => null,
            'section' => null,
        ]);

        $inScope = User::factory()->create([
            'role' => UserRole::Student,
            'is_active' => true,
            'student_status' => StudentStatus::Enrolled,
            'grade_level' => '10',
            'section' => 'A',
        ]);

        $outOfScope = User::factory()->create([
            'role' => UserRole::Student,
            'is_active' => true,
            'student_status' => StudentStatus::Enrolled,
            'grade_level' => '12',
            'section' => 'C',
        ]);

        $ids = app(AdminScopeService::class)
            ->scopedStudentsQuery($admin)
            ->pluck('id');

        $this->assertTrue($ids->contains($unassigned->id));
        $this->assertTrue($ids->contains($inScope->id));
        $this->assertFalse($ids->contains($outOfScope->id));

        $manageableIds = app(AdminScopeService::class)
            ->manageableStudentsQuery($admin)
            ->pluck('id');

        $this->assertTrue($manageableIds->contains($outOfScope->id));
    }

    public function test_statistics_count_election_votes_even_when_voter_outside_grade_scope(): void
    {
        $admin = User::factory()->admin()->create();
        $election = Election::factory()->create([
            'status' => ElectionStatus::Active,
            'created_by' => $admin->id,
        ]);

        AdminAssignment::query()->create([
            'user_id' => $admin->id,
            'election_id' => $election->id,
            'grade_levels' => ['10'],
            'sections' => ['A'],
            'assigned_by' => $admin->id,
        ]);

        $voter = User::factory()->create([
            'role' => UserRole::Student,
            'is_active' => true,
            'student_status' => StudentStatus::Enrolled,
            'grade_level' => '12',
            'section' => 'C',
        ]);

        $otherEligible = User::factory()->create([
            'role' => UserRole::Student,
            'is_active' => true,
            'student_status' => StudentStatus::Enrolled,
            'grade_level' => '11',
            'section' => 'B',
        ]);

        $category = ElectionCategory::query()->create([
            'election_id' => $election->id,
            'name' => 'President',
            'slug' => 'president',
            'sort_order' => 1,
            'max_selections' => 1,
        ]);

        $candidate = Candidate::query()->create([
            'election_id' => $election->id,
            'election_category_id' => $category->id,
            'display_name' => 'Test Candidate',
            'position' => 'President',
            'eligibility_status' => 'verified',
            'is_active' => true,
        ]);

        Vote::withoutEvents(function () use ($voter, $election, $category, $candidate) {
            Vote::query()->create([
                'user_id' => $voter->id,
                'election_id' => $election->id,
                'election_category_id' => $category->id,
                'candidate_id' => $candidate->id,
                'voted_at' => now(),
            ]);
        });

        $stats = app(AdminScopeService::class)->statistics($admin);
        $breakdown = app(AdminScopeService::class)->voterBreakdown($admin);

        $this->assertSame(2, $stats['eligible_voters']);
        $this->assertSame(1, $stats['votes_cast']);
        $this->assertSame(50.0, $stats['turnout_percent']);
        $this->assertSame(2, $breakdown['eligible']);
        $this->assertSame(1, $breakdown['voted']);
        $this->assertSame(1, $breakdown['notVoted']);
        $this->assertNotNull($otherEligible->id);
    }

    public function test_turnout_by_section_is_election_wide_and_exposes_grade_keys(): void
    {
        $admin = User::factory()->admin()->create();
        $election = Election::factory()->create([
            'status' => ElectionStatus::Active,
            'created_by' => $admin->id,
        ]);

        AdminAssignment::query()->create([
            'user_id' => $admin->id,
            'election_id' => $election->id,
            'grade_levels' => ['10'],
            'sections' => ['A'],
            'assigned_by' => $admin->id,
        ]);

        $voter = User::factory()->create([
            'role' => UserRole::Student,
            'is_active' => true,
            'student_status' => StudentStatus::Enrolled,
            'grade_level' => '12',
            'section' => 'C',
        ]);

        User::factory()->create([
            'role' => UserRole::Student,
            'is_active' => true,
            'student_status' => StudentStatus::Enrolled,
            'grade_level' => '11',
            'section' => 'B',
        ]);

        $category = ElectionCategory::query()->create([
            'election_id' => $election->id,
            'name' => 'President',
            'slug' => 'president-turnout',
            'sort_order' => 1,
            'max_selections' => 1,
        ]);

        $candidate = Candidate::query()->create([
            'election_id' => $election->id,
            'election_category_id' => $category->id,
            'display_name' => 'Turnout Candidate',
            'position' => 'President',
            'eligibility_status' => 'verified',
            'is_active' => true,
        ]);

        Vote::withoutEvents(function () use ($voter, $election, $category, $candidate) {
            Vote::query()->create([
                'user_id' => $voter->id,
                'election_id' => $election->id,
                'election_category_id' => $category->id,
                'candidate_id' => $candidate->id,
                'voted_at' => now(),
            ]);
        });

        $sections = app(AdminScopeService::class)->turnoutBySection($admin);

        $this->assertCount(2, $sections);

        $voterRow = $sections->firstWhere('grade', '12');
        $this->assertNotNull($voterRow);
        $this->assertSame('C', $voterRow['section']);
        $this->assertSame(1, $voterRow['voted']);
        $this->assertSame(1, $voterRow['eligible']);
        $this->assertSame(100.0, $voterRow['turnout']);
        $this->assertSame(100.0, $voterRow['turnout_percent']);
        $this->assertSame(1, $voterRow['registered']);
    }

    public function test_assigned_election_ignores_soft_deleted_elections(): void
    {
        $admin = User::factory()->admin()->create();
        $election = Election::factory()->active()->create([
            'title' => 'Deleted Council',
            'created_by' => $admin->id,
        ]);

        AdminAssignment::query()->create([
            'user_id' => $admin->id,
            'election_id' => $election->id,
            'assigned_by' => $admin->id,
        ]);

        $this->assertSame($election->id, app(AdminScopeService::class)->assignedElection($admin)?->id);

        $election->delete();

        $this->assertNull(app(AdminScopeService::class)->assignedElection($admin));
        $this->assertTrue($election->trashed());
    }

    public function test_super_admin_can_host_talent_on_closed_elections(): void
    {
        $super = User::factory()->superAdmin()->create();
        $closed = Election::factory()->closed()->create([
            'title' => 'Closed Student Council',
            'created_by' => $super->id,
        ]);
        $annulled = Election::factory()->closed()->create([
            'title' => 'Annulled Council',
            'annulled_at' => now(),
            'created_by' => $super->id,
        ]);

        $hosts = app(AdminScopeService::class)->talentHostElections($super);

        $this->assertTrue($hosts->contains('id', $closed->id));
        $this->assertFalse($hosts->contains('id', $annulled->id));
        $this->assertSame(
            $closed->id,
            app(AdminScopeService::class)->resolveTalentHostElection($super, $closed->id)?->id
        );
    }
}
