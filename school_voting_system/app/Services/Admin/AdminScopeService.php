<?php

namespace App\Services\Admin;

use App\Enums\ElectionStatus;
use App\Enums\StudentStatus;
use App\Enums\TalentEventStatus;
use App\Enums\UserRole;
use App\Models\AdminAssignment;
use App\Models\AdminComplaint;
use App\Models\AdminVerificationRequest;
use App\Models\AuditLog;
use App\Models\Candidate;
use App\Models\Event;
use App\Models\Election;
use App\Models\Fundraiser;
use App\Models\Partylist;
use App\Models\PartylistPoster;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\TalentEventVote;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AdminScopeService
{
    public function assignment(User $admin): ?AdminAssignment
    {
        return AdminAssignment::query()
            ->with('election')
            ->where('user_id', $admin->id)
            ->first();
    }

    public function assignElectionToAdmin(User $admin, Election $election, ?int $assignedBy = null): AdminAssignment
    {
        return AdminAssignment::query()->updateOrCreate(
            ['user_id' => $admin->id],
            [
                'election_id' => $election->id,
                'assigned_by' => $assignedBy,
            ],
        );
    }

    public function assignedElection(User $admin): ?Election
    {
        $assignment = $this->assignment($admin);

        if ($assignment?->election) {
            return $assignment->election;
        }

        if ($admin->isSuperAdmin()) {
            return Election::query()
                ->whereIn('status', [ElectionStatus::Active, ElectionStatus::Draft])
                ->latest()
                ->first();
        }

        return null;
    }

    public function assertElectionInScope(User $admin, Election $election): void
    {
        if ($admin->isSuperAdmin()) {
            return;
        }

        abort_unless(
            $this->assignment($admin)?->election_id === $election->id,
            403,
            'This election is outside your assigned scope.'
        );
    }

    /**
     * Active students used for turnout, reminders, and election monitoring.
     * Super Admins see the full active roster. Assigned admins include students
     * with matching grade/section plus unassigned (null/empty) records so voters
     * are not hidden from stats after casting a ballot.
     */
    public function scopedStudentsQuery(User $admin): Builder
    {
        $query = User::query()
            ->where('role', UserRole::Student)
            ->where('is_active', true);

        if ($admin->isSuperAdmin()) {
            return $query;
        }

        return $this->applyAssignmentStudentFilters($query, $admin, includeUnassigned: true);
    }

    /**
     * Students an administrator may view and assign grade/section for.
     * Includes unassigned students (null/empty grade or section) within admin scope.
     */
    public function manageableStudentsQuery(User $admin): Builder
    {
        if ($admin->isSuperAdmin()) {
            return User::query()->where('role', UserRole::Student);
        }

        $query = User::query()
            ->where('role', UserRole::Student)
            ->where('is_active', true);

        return $this->applyAssignmentStudentFilters($query, $admin, includeUnassigned: true);
    }

    /**
     * Apply grade/section filters from the admin assignment.
     *
     * @param  bool  $includeUnassigned  When true, null/empty grade or section still match.
     */
    protected function applyAssignmentStudentFilters(Builder $query, User $admin, bool $includeUnassigned = true): Builder
    {
        $assignment = $this->assignment($admin);

        if ($assignment?->grade_levels) {
            $grades = $assignment->grade_levels;
            if ($includeUnassigned) {
                $query->where(function (Builder $q) use ($grades) {
                    $q->whereNull('grade_level')
                        ->orWhere('grade_level', '')
                        ->orWhereIn('grade_level', $grades);
                });
            } else {
                $query->whereIn('grade_level', $grades);
            }
        }

        if ($assignment?->sections) {
            $sections = $assignment->sections;
            if ($includeUnassigned) {
                $query->where(function (Builder $q) use ($sections) {
                    $q->whereNull('section')
                        ->orWhere('section', '')
                        ->orWhereIn('section', $sections);
                });
            } else {
                $query->whereIn('section', $sections);
            }
        }

        return $query;
    }

    public function canManageStudent(User $admin, User $student): bool
    {
        if (! $student->isStudent()) {
            return false;
        }

        if ($admin->isSuperAdmin()) {
            return true;
        }

        return $this->manageableStudentsQuery($admin)
            ->whereKey($student->id)
            ->exists();
    }

    /**
     * @return list<string>
     */
    public function assignableGradeLevels(User $admin): array
    {
        if ($admin->isSuperAdmin()) {
            return [];
        }

        return array_values($this->assignment($admin)?->grade_levels ?? []);
    }

    /**
     * @return list<string>
     */
    public function assignableSections(User $admin): array
    {
        if ($admin->isSuperAdmin()) {
            return [];
        }

        return array_values($this->assignment($admin)?->sections ?? []);
    }

    public function statistics(User $admin): array
    {
        $election = $this->assignedElection($admin);

        // Election monitors use school-wide eligible + all ballots for that election
        // so assigned grade/section filters cannot hide real votes from the dashboard.
        if ($election) {
            $eligible = $election->eligibleVoterCount();
            $votesCast = (int) $election->votes()->count();
            $voted = (int) $election->votes()->distinct('user_id')->count('user_id');
        } else {
            $eligible = $this->scopedStudentsQuery($admin)
                ->where('student_status', StudentStatus::Enrolled)
                ->count();
            $voted = 0;
            $votesCast = 0;
        }

        $turnout = $eligible > 0 ? round(($voted / $eligible) * 100, 1) : 0.0;

        $partylistQuery = Partylist::query();
        $candidateQuery = Candidate::query();

        if ($election) {
            $partylistQuery->whereHas('elections', fn ($q) => $q->whereKey($election->id));
            $candidateQuery->where('election_id', $election->id);
        }

        $pendingVerifications = AdminVerificationRequest::query()
            ->where('assigned_to', $admin->id)
            ->where('status', 'pending')
            ->count();

        $pendingComplaints = AdminComplaint::query()
            ->where('assigned_to', $admin->id)
            ->where('status', 'open')
            ->count();

        $pendingPosters = $election
            ? PartylistPoster::query()
                ->where('election_id', $election->id)
                ->where('status', PartylistPoster::STATUS_PENDING)
                ->count()
            : 0;

        $activeFundraisers = Fundraiser::query()->acceptingDonations()->count();

        return [
            'assigned_election' => $election?->title ?? 'No assignment',
            'election_status' => $this->electionStatusLabel($election),
            'eligible_voters' => $eligible,
            'votes_cast' => $votesCast,
            'turnout_percent' => $turnout,
            'partylists' => $partylistQuery->count(),
            'candidates' => $candidateQuery->count(),
            'pending_verifications' => $pendingVerifications,
            'pending_complaints' => $pendingComplaints,
            'pending_items' => $pendingVerifications + $pendingComplaints,
            'pending_posters' => $pendingPosters,
            'active_fundraisers' => $activeFundraisers,
            'total_fundraisers' => Fundraiser::query()->count(),
        ];
    }

    /**
     * Active enrolled students used for election-wide turnout and analytics.
     */
    public function eligibleStudentsQuery(): Builder
    {
        return User::query()
            ->where('role', UserRole::Student)
            ->where('is_active', true)
            ->where('student_status', StudentStatus::Enrolled);
    }

    /**
     * Turnout by grade/section for the assigned election (school-wide roster).
     *
     * @return Collection<int, array{
     *     label: string,
     *     grade: string,
     *     section: string,
     *     eligible: int,
     *     registered: int,
     *     voted: int,
     *     turnout: float,
     *     turnout_percent: float
     * }>
     */
    public function turnoutBySection(User $admin): Collection
    {
        $election = $this->assignedElection($admin);

        if (! $election) {
            return collect();
        }

        $students = $this->eligibleStudentsQuery()->get(['id', 'section', 'grade_level']);

        if ($students->isEmpty()) {
            return collect();
        }

        $votedIds = Vote::query()
            ->where('election_id', $election->id)
            ->whereIn('user_id', $students->pluck('id'))
            ->pluck('user_id')
            ->unique();

        return $students
            ->groupBy(fn ($s) => (filled($s->grade_level) ? $s->grade_level : 'All').' · '.(filled($s->section) ? $s->section : 'General'))
            ->map(function ($group, $label) use ($votedIds) {
                $eligible = $group->count();
                $voted = $group->whereIn('id', $votedIds)->count();
                $turnout = $eligible > 0 ? round(($voted / $eligible) * 100, 1) : 0.0;
                $first = $group->first();

                return [
                    'label' => $label,
                    'grade' => filled($first->grade_level) ? (string) $first->grade_level : 'All',
                    'section' => filled($first->section) ? (string) $first->section : 'General',
                    'eligible' => $eligible,
                    'registered' => $eligible,
                    'voted' => $voted,
                    'turnout' => $turnout,
                    'turnout_percent' => $turnout,
                ];
            })
            ->values();
    }

    public function voterBreakdown(User $admin): array
    {
        $election = $this->assignedElection($admin);

        if ($election) {
            $eligible = $election->eligibleVoterCount();
            $voted = (int) $election->votes()->distinct('user_id')->count('user_id');
            $notVoted = max(0, $eligible - $voted);
            $ineligible = User::query()
                ->where('role', UserRole::Student)
                ->where('is_active', true)
                ->where('student_status', '!=', StudentStatus::Enrolled)
                ->count();

            return compact('eligible', 'voted', 'notVoted', 'ineligible');
        }

        $base = $this->scopedStudentsQuery($admin);

        $eligible = (clone $base)->where('student_status', StudentStatus::Enrolled)->count();
        $ineligible = (clone $base)->where('student_status', '!=', StudentStatus::Enrolled)->count();
        $voted = 0;
        $notVoted = $eligible;

        return compact('eligible', 'voted', 'notVoted', 'ineligible');
    }

    /**
     * Mini-chart series for dashboard stat cards (line, bar, and donut).
     *
     * @return array<string, array<string, mixed>>
     */
    public function statCardSparklines(User $admin): array
    {
        $election = $this->assignedElection($admin);
        $eligibleIds = $this->eligibleStudentsQuery()->pluck('id');
        $eligible = max(1, $eligibleIds->count());
        $breakdown = $this->voterBreakdown($admin);
        $turnoutPercent = $breakdown['eligible'] > 0
            ? round(($breakdown['voted'] / $breakdown['eligible']) * 100, 1)
            : 0.0;

        $lineDays = collect(range(6, 0))->map(fn (int $offset) => now()->subDays($offset)->endOfDay());
        $barDays = collect(range(4, 0))->map(fn (int $offset) => now()->subDays($offset));
        $weekEnds = collect(range(4, 0))->map(fn (int $offset) => now()->subWeeks($offset)->endOfWeek());

        $turnoutLine = array_fill(0, 7, 0.0);
        $notVotedLine = array_map('floatval', array_fill(0, 7, $breakdown['notVoted']));
        $votesBars = array_fill(0, 5, 0);

        if ($election && $eligibleIds->isNotEmpty()) {
            $firstVotes = Vote::query()
                ->where('election_id', $election->id)
                ->whereIn('user_id', $eligibleIds)
                ->selectRaw('user_id, MIN(voted_at) as first_vote')
                ->groupBy('user_id')
                ->pluck('first_vote')
                ->map(fn ($at) => Carbon::parse($at));

            $turnoutLine = $lineDays->map(function (Carbon $dayEnd) use ($firstVotes, $eligible) {
                $voted = $firstVotes->filter(fn (Carbon $at) => $at->lte($dayEnd))->count();

                return round(($voted / $eligible) * 100, 1);
            })->values()->all();

            $notVotedLine = $lineDays->map(function (Carbon $dayEnd) use ($firstVotes, $eligible) {
                $voted = $firstVotes->filter(fn (Carbon $at) => $at->lte($dayEnd))->count();

                return (float) max(0, $eligible - $voted);
            })->values()->all();

            $dailyVotes = Vote::query()
                ->where('election_id', $election->id)
                ->whereIn('user_id', $eligibleIds)
                ->where('voted_at', '>=', $barDays->first()->copy()->startOfDay())
                ->selectRaw('DATE(voted_at) as vote_date, COUNT(*) as total')
                ->groupBy('vote_date')
                ->pluck('total', 'vote_date');

            $votesBars = $barDays->map(
                fn (Carbon $day) => (int) ($dailyVotes[$day->toDateString()] ?? 0),
            )->values()->all();
        }

        $partylistQuery = Partylist::query();
        $candidateQuery = Candidate::query();

        if ($election) {
            $partylistQuery->whereHas('elections', fn ($q) => $q->whereKey($election->id));
            $candidateQuery->where('election_id', $election->id);
        }

        $partylists = $partylistQuery->get(['id', 'created_at']);
        $campaignBars = $weekEnds->map(
            fn (Carbon $end) => $partylists->filter(
                fn (Partylist $partylist) => Carbon::parse($partylist->created_at)->lte($end),
            )->count(),
        )->values()->all();

        $totalCandidates = (clone $candidateQuery)->count();
        $verifiedCandidates = (clone $candidateQuery)
            ->where('eligibility_status', 'verified')
            ->where('is_active', true)
            ->count();
        $candidatePercent = $totalCandidates > 0
            ? round(($verifiedCandidates / $totalCandidates) * 100, 1)
            : 0.0;

        return [
            'turnout_percent' => ['type' => 'line', 'values' => $turnoutLine],
            'votes_cast' => ['type' => 'bars', 'values' => $votesBars],
            'eligible_voters' => ['type' => 'donut', 'percent' => $turnoutPercent],
            'not_voted' => ['type' => 'line', 'values' => $notVotedLine],
            'partylists' => ['type' => 'bars', 'values' => $campaignBars],
            'candidates' => ['type' => 'donut', 'percent' => $candidatePercent],
        ];
    }

    public function partylists(User $admin): Collection
    {
        $election = $this->assignedElection($admin);

        $query = Partylist::query()
            ->with(['posters' => fn ($q) => $q->latest()])
            ->active();

        if ($election) {
            $query->whereHas('elections', fn ($q) => $q->whereKey($election->id));
        }

        return $query->orderBy('name')->get();
    }

    public function pendingPosters(User $admin): Collection
    {
        $election = $this->assignedElection($admin);

        if (! $election) {
            return collect();
        }

        return PartylistPoster::query()
            ->with('partylist')
            ->where('election_id', $election->id)
            ->where('status', PartylistPoster::STATUS_PENDING)
            ->latest()
            ->get();
    }

    public function candidates(User $admin, ?string $status = null): Collection
    {
        $election = $this->assignedElection($admin);

        $query = Candidate::query()->with(['election', 'category']);

        if ($election) {
            $query->where('election_id', $election->id);
        }

        if ($status) {
            $query->where('eligibility_status', $status);
        }

        return $query->latest()->limit(30)->get();
    }

    public function notVotedStudents(User $admin): Collection
    {
        $election = $this->assignedElection($admin);

        if (! $election) {
            return collect();
        }

        $eligible = $this->scopedStudentsQuery($admin)
            ->where('student_status', StudentStatus::Enrolled)
            ->get();

        $votedIds = Vote::query()
            ->where('election_id', $election->id)
            ->pluck('user_id');

        return $eligible->whereNotIn('id', $votedIds)->values();
    }

    public function myActivityLogs(User $admin, ?string $from = null, ?string $to = null, ?string $type = null): Collection
    {
        $query = AuditLog::query()
            ->where('user_id', $admin->id)
            ->latest();

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        if ($type) {
            $query->where('action_type', $type);
        }

        return $query->limit(50)->get();
    }

    public function myActivityLogsQuery(User $admin, ?string $from = null, ?string $to = null, ?string $type = null): \Illuminate\Database\Eloquent\Builder
    {
        $query = AuditLog::query()
            ->where('user_id', $admin->id)
            ->latest();

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        if ($type) {
            $query->where('action_type', $type);
        }

        return $query;
    }

    public function auditLogsQuery(
        User $admin,
        ?string $search = null,
        ?string $from = null,
        ?string $to = null,
        ?string $module = null,
        ?string $role = null,
    ): \Illuminate\Database\Eloquent\Builder {
        $query = AuditLog::query()->with('user')->latest();

        if (! $admin->isSuperAdmin()) {
            $election = $this->assignedElection($admin);

            $query->where(function ($inner) use ($admin, $election) {
                $inner->where('user_id', $admin->id);

                if ($election) {
                    $inner->orWhere(function ($scoped) use ($election) {
                        $scoped->where('target_type', 'election')
                            ->where('target_id', $election->id);
                    })->orWhere('action', 'like', '%'.$election->title.'%');
                }
            });
        }

        if ($search) {
            $query->where(function ($inner) use ($search) {
                $inner->where('action', 'like', '%'.$search.'%')
                    ->orWhere('admin_name', 'like', '%'.$search.'%');
            });
        }

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        if ($module) {
            $query->where('action_type', $module);
        }

        if ($role) {
            $query->where('admin_role', 'like', '%'.$role.'%');
        }

        return $query;
    }

    public function auditLogModules(): Collection
    {
        return AuditLog::query()
            ->whereNotNull('action_type')
            ->distinct()
            ->orderBy('action_type')
            ->pluck('action_type');
    }

    public function activityLogActionTypes(User $admin): Collection
    {
        return AuditLog::query()
            ->where('user_id', $admin->id)
            ->whereNotNull('action_type')
            ->distinct()
            ->orderBy('action_type')
            ->pluck('action_type');
    }

    public function pendingVerificationRequests(User $admin): Collection
    {
        $query = AdminVerificationRequest::query()
            ->with('subject')
            ->where('status', 'pending');

        if (! $admin->isSuperAdmin()) {
            $query->where('assigned_to', $admin->id);
        }

        return $query->latest()->limit(20)->get();
    }

    public function openComplaints(User $admin): Collection
    {
        $query = AdminComplaint::query()
            ->with('election')
            ->where('status', 'open');

        if (! $admin->isSuperAdmin()) {
            $query->where('assigned_to', $admin->id);
        }

        return $query->latest()->limit(20)->get();
    }

    public function isAuditor(User $admin): bool
    {
        return $admin->staffRole?->slug === 'auditor';
    }

    public function isReadOnly(User $admin): bool
    {
        if ($admin->isSuperAdmin()) {
            return false;
        }

        return $admin->staffRole?->slug === 'read_only_admin';
    }

    public function canExportPreliminaryResults(User $admin): bool
    {
        return $admin->hasPermission('export_reports')
            && $this->assignedElection($admin) !== null;
    }

    public function canPauseElection(User $admin): bool
    {
        return ! $this->isReadOnly($admin)
            && ! $this->isAuditor($admin)
            && $admin->hasPermission('pause_election');
    }

    public function canApprovePosters(User $admin): bool
    {
        if ($admin->isSuperAdmin()) {
            return true;
        }

        return ! $this->isReadOnly($admin)
            && ! $this->isAuditor($admin)
            && $admin->hasPermission('approve_posters');
    }

    public function canVerifyCandidates(User $admin): bool
    {
        return ! $this->isReadOnly($admin)
            && ! $this->isAuditor($admin)
            && $admin->hasPermission('verify_candidates');
    }

    public function canSendReminders(User $admin): bool
    {
        return ! $this->isReadOnly($admin)
            && ! $this->isAuditor($admin)
            && $admin->hasPermission('send_reminders');
    }

    public function canApproveTalentEntries(User $admin): bool
    {
        return ! $this->isReadOnly($admin)
            && ! $this->isAuditor($admin)
            && $admin->hasPermission('approve_talent_entries');
    }

    public function canManageTalentVoting(User $admin): bool
    {
        return ! $this->isReadOnly($admin)
            && ! $this->isAuditor($admin)
            && $admin->hasPermission('manage_talent_voting');
    }

    public function canPublishTalentResults(User $admin): bool
    {
        return ! $this->isReadOnly($admin)
            && ! $this->isAuditor($admin)
            && $admin->hasPermission('publish_talent_results');
    }

    public function canPublishElectionResults(User $admin): bool
    {
        if ($this->isReadOnly($admin) || $this->isAuditor($admin)) {
            return false;
        }

        return $admin->isSuperAdmin() || $admin->hasPermission('publish_election_results');
    }

    public function canUnpublishElectionResults(User $admin): bool
    {
        return $this->canPublishElectionResults($admin);
    }

    public function canCreateTalentEvents(User $admin): bool
    {
        return ! $this->isReadOnly($admin)
            && ! $this->isAuditor($admin)
            && $admin->hasPermission('create_talent_events');
    }

    public function canViewRealtimeTalentCounts(User $admin): bool
    {
        if ($this->isReadOnly($admin) || $this->isAuditor($admin)) {
            return false;
        }

        return $admin->isSuperAdmin() || $admin->isAdmin();
    }

    public function canViewTalentVoteCounts(User $admin, TalentEvent $event): bool
    {
        if (! $admin->isSuperAdmin() && ! $admin->isAdmin()) {
            return false;
        }

        if (! $admin->isSuperAdmin()) {
            $assignedId = $this->assignment($admin)?->election_id;
            $ownsEvent = (int) $event->created_by === (int) $admin->id;
            $inAssignedElection = $assignedId && (int) $event->election_id === (int) $assignedId;

            if (! $ownsEvent && ! $inAssignedElection) {
                return false;
            }
        }

        if ($event->votingHasClosed()
            || $event->status === TalentEventStatus::ResultsPublished
            || $event->status === TalentEventStatus::Completed) {
            return true;
        }

        return $this->canViewRealtimeTalentCounts($admin);
    }

    public function fundraisers(User $admin, int $limit = 6): Collection
    {
        return Fundraiser::query()
            ->withCount('donations')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function talentEvents(User $admin): Collection
    {
        if ($admin->isSuperAdmin()) {
            return TalentEvent::query()
                ->with([
                    'entries' => fn ($q) => $q->withCount('votes')->orderBy('display_name'),
                ])
                ->withCount(['votes', 'entries'])
                ->orderByDesc('event_date')
                ->get();
        }

        $assignedId = $this->assignment($admin)?->election_id;
        $election = $this->assignedElection($admin);
        $scopedStudentIds = $election
            ? $this->scopedStudentsQuery($admin)->pluck('id')
            : collect();

        $query = TalentEvent::query()
            ->where(function ($inner) use ($admin, $assignedId) {
                $inner->where('created_by', $admin->id);

                if ($assignedId) {
                    $inner->orWhere('election_id', $assignedId);
                }
            })
            ->orderByDesc('event_date');

        if ($scopedStudentIds->isNotEmpty()) {
            $query
                ->with([
                    'entries' => fn ($q) => $q
                        ->withCount([
                            'votes' => fn ($vq) => $vq->whereIn('user_id', $scopedStudentIds),
                        ])
                        ->orderBy('display_name'),
                ])
                ->withCount([
                    'votes' => fn ($q) => $q->whereIn('user_id', $scopedStudentIds),
                    'entries',
                ]);
        } else {
            $query
                ->with([
                    'entries' => fn ($q) => $q->withCount('votes')->orderBy('display_name'),
                ])
                ->withCount(['votes', 'entries']);
        }

        return $query->get();
    }

    public function schoolEvents(User $admin): Collection
    {
        return Event::query()
            ->latest('event_date')
            ->get();
    }

    public function assertTalentEventInScope(User $admin, TalentEvent $event): void
    {
        if ($admin->isSuperAdmin()) {
            return;
        }

        if ((int) $event->created_by === (int) $admin->id) {
            return;
        }

        $assignedId = $this->assignment($admin)?->election_id;

        abort_unless(
            $assignedId && (int) $event->election_id === (int) $assignedId,
            403,
            'This competition is outside your assigned scope.'
        );
    }

    public function assertTalentEntryInScope(User $admin, TalentEventEntry $entry): void
    {
        $entry->loadMissing('talentEvent');
        $this->assertTalentEventInScope($admin, $entry->talentEvent);
    }

    public function auditorChecks(User $admin): array
    {
        if (! $this->isAuditor($admin)) {
            return [];
        }

        $election = $this->assignedElection($admin);
        $eligible = $election
            ? $election->eligibleVoterCount()
            : $this->scopedStudentsQuery($admin)
                ->where('student_status', StudentStatus::Enrolled)
                ->count();

        $votes = $election
            ? (int) $election->votes()->count()
            : 0;

        $duplicateAttempts = AuditLog::query()
            ->where('action', 'like', '%duplicate%')
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->count();

        $pendingPosters = $election
            ? PartylistPoster::query()
                ->where('election_id', $election->id)
                ->where('status', PartylistPoster::STATUS_PENDING)
                ->count()
            : 0;

        return [
            'votes_cast' => $votes,
            'eligible_voters' => $eligible,
            'balance_ok' => $votes <= $eligible,
            'duplicate_attempts' => $duplicateAttempts,
            'pending_posters' => $pendingPosters,
            'verified_results_only' => true,
        ];
    }

    public function roleGuide(User $admin): array
    {
        $slug = $admin->staffRole?->slug ?? 'election_admin';

        return match ($slug) {
            'auditor' => [
                'can' => ['View verified results', 'Cross-check turnout', 'Flag anomalies', 'View own activity log'],
                'cannot' => ['Approve posters', 'Verify candidates', 'Pause voting', 'Modify elections'],
            ],
            'student_records_admin' => [
                'can' => ['Verify candidates', 'Send voting reminders', 'View assigned voter lists'],
                'cannot' => ['Approve posters', 'Pause elections', 'Edit partylist data', 'Access system settings'],
            ],
            'read_only_admin' => [
                'can' => ['View assigned dashboard', 'Export unofficial results'],
                'cannot' => ['Approve/reject posters', 'Verify candidates', 'Send reminders', 'Pause voting'],
            ],
            default => [
                'can' => ['Pause/Resume voting', 'Approve/reject posters', 'Verify candidates', 'Manage talent event voting', 'Publish talent results', 'Create and manage fundraisers'],
                'cannot' => ['Edit partylist records directly', 'Close/Annul elections', 'Access Super Admin data'],
            ],
        };
    }

    protected function electionStatusLabel(?Election $election): string
    {
        if (! $election) {
            return 'Unassigned';
        }

        if ($election->annulled_at) {
            return 'Annulled';
        }

        if ($election->is_paused) {
            return 'Paused';
        }

        return ucfirst($election->status?->value ?? 'draft');
    }

    public function countdown(?Election $election): ?array
    {
        $snapshot = $election?->countdownSnapshot();

        if (! $snapshot) {
            return null;
        }

        return [
            'label' => $snapshot['label'],
            'remaining' => $snapshot['remaining'],
            'phase' => $snapshot['phase'],
            'target_at_iso' => $snapshot['target_at_iso'],
            'ends_at' => $election->voting_ends_at?->toDayDateTimeString(),
            'ends_at_iso' => $snapshot['ends_at_iso'],
            'starts_at_iso' => $snapshot['starts_at_iso'],
            'is_closed' => $snapshot['is_closed'],
        ];
    }

    public function assertPosterInScope(User $admin, PartylistPoster $poster): void
    {
        if ($admin->isSuperAdmin()) {
            return;
        }

        $election = $this->assignedElection($admin);
        abort_unless($election && $poster->election_id === $election->id, 403);
    }

    public function assertPartylistInScope(User $admin, Partylist $partylist): void
    {
        if ($admin->isSuperAdmin()) {
            return;
        }

        // Campaigns are a shared, reusable pool; any managing admin may edit
        // them. Read-only and auditor admins remain blocked.
        abort_if($this->isReadOnly($admin) || $this->isAuditor($admin), 403);
    }
}
