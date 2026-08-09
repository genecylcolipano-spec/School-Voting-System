<?php

namespace App\Http\Controllers\Student;

use App\Enums\AuditActionType;
use App\Exceptions\DonationIntegrityException;
use App\Exceptions\VoteIntegrityException;
use App\Http\Controllers\Concerns\ManagesPortalNotifications;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitBallotRequest;
use App\Models\Announcement;
use App\Models\AnnouncementAttachment;
use App\Models\BallotSubmission;
use App\Models\Candidate;
use App\Models\Donation;
use App\Models\Election;
use App\Models\ElectionCategory;
use App\Models\Event;
use App\Models\Fundraiser;
use App\Models\Partylist;
use App\Models\PortalNotification;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\TalentEventVote;
use App\Models\Vote;
use App\Support\EventImageUrl;
use App\Services\Campaign\StudentCampaignService;
use App\Services\Election\StudentElectionService;
use App\Services\Portal\AnnouncementService;
use App\Services\Portal\PortalNotificationService;
use App\Services\Student\StudentResultsService;
use App\Services\SuperAdmin\AuditLogService;
use App\Services\Talent\StudentTalentHeroActionResolver;
use App\Services\Talent\StudentTalentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentPortalController extends Controller
{
    use ManagesPortalNotifications;

    public function __construct(
        protected StudentTalentService $talentService,
        protected StudentCampaignService $campaignService,
        protected StudentElectionService $electionService,
        protected StudentResultsService $resultsService,
        protected PortalNotificationService $notifications,
        protected AnnouncementService $announcements,
        protected AuditLogService $audit,
    ) {}

    public function events(Request $request): View
    {
        $events = Event::query()
            ->orderBy('event_date')
            ->paginate(12);

        return view('student.events.index', [
            'user' => $request->user()->loadCount('passkeys'),
            'events' => $events,
        ]);
    }

    public function statistics(Request $request): View
    {
        $user = $request->user()->loadCount(['passkeys', 'votes', 'donations']);

        $electionsJoined = (int) $user->votes()->distinct()->count('election_id');
        $competitionsJoined = TalentEventEntry::query()
            ->where('user_id', $user->id)
            ->distinct()
            ->count('talent_event_id');
        $fundraisersSupported = Donation::query()
            ->where('user_id', $user->id)
            ->distinct()
            ->count('fundraiser_id');
        $totalDonated = (float) $user->donations()->sum('amount');

        $eligibleElections = Election::query()
            ->whereIn('status', [
                \App\Enums\ElectionStatus::Active,
                \App\Enums\ElectionStatus::Closed,
            ])
            ->count();

        $openCategoryIds = ElectionCategory::query()
            ->whereHas('election', fn ($query) => $query->acceptingVotes())
            ->pluck('id');
        $openCategoryCount = $openCategoryIds->count();
        $votesInOpenElections = $openCategoryCount > 0
            ? $user->votes()->whereIn('election_category_id', $openCategoryIds)->count()
            : 0;

        $lifetimeVotingPercent = $eligibleElections > 0
            ? (int) round(($electionsJoined / $eligibleElections) * 100)
            : 0;
        $openVotingPercent = $openCategoryCount > 0
            ? (int) round(($votesInOpenElections / $openCategoryCount) * 100)
            : 0;

        $lastVote = Vote::query()
            ->with(['election:id,title,slug'])
            ->where('user_id', $user->id)
            ->orderByDesc('voted_at')
            ->first();

        $lastCompetition = TalentEventEntry::query()
            ->with(['talentEvent:id,title,slug'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->first();

        $lastDonation = Donation::query()
            ->with(['fundraiser:id,title,slug'])
            ->where('user_id', $user->id)
            ->orderByDesc('donated_at')
            ->first();

        $recentDonations = Donation::query()
            ->with(['fundraiser:id,title,slug'])
            ->where('user_id', $user->id)
            ->orderByDesc('donated_at')
            ->limit(5)
            ->get();

        $publishedCompetitions = TalentEvent::query()->publishedToStudents()->count();
        $visibleFundraisers = Fundraiser::query()->count();

        $engagement = [
            'voting' => min(100, $lifetimeVotingPercent),
            'events' => null, // no event registration module yet
            'competitions' => $publishedCompetitions > 0
                ? (int) round(($competitionsJoined / $publishedCompetitions) * 100)
                : 0,
            'fundraising' => $visibleFundraisers > 0
                ? (int) round(($fundraisersSupported / $visibleFundraisers) * 100)
                : 0,
        ];

        $totalActivities = $electionsJoined + $competitionsJoined + $fundraisersSupported + $user->passkeys_count;
        $participationLevel = match (true) {
            $totalActivities >= 15 => 'Excellent',
            $totalActivities >= 8 => 'Very Active',
            $totalActivities >= 3 => 'Active',
            default => 'New Participant',
        };

        $notificationsCount = $this->notifications->unreadCountFor($user);

        return view('student.statistics.index', [
            'user' => $user,
            'notificationsCount' => $notificationsCount,
            'overview' => [
                'votes_cast' => $user->votes_count,
                'elections_joined' => $electionsJoined,
                'competitions_joined' => $competitionsJoined,
                'fundraisers_supported' => $fundraisersSupported,
            ],
            'activitySummary' => [
                'recent_vote' => $lastVote?->election?->title,
                'last_event' => null,
                'last_competition' => $lastCompetition?->talentEvent?->title,
                'last_donation' => $lastDonation?->fundraiser?->title,
                'passkeys' => $user->passkeys_count,
                'member_since' => $user->created_at,
            ],
            'votingAnalytics' => [
                'has_open_elections' => $openCategoryCount > 0,
                'has_history' => $electionsJoined > 0 || $user->votes_count > 0,
                'elections_joined' => $electionsJoined,
                'eligible_elections' => $eligibleElections,
                'lifetime_percent' => $lifetimeVotingPercent,
                'open_percent' => $openVotingPercent,
                'completed_votes' => $user->votes_count,
                'votes_in_open' => $votesInOpenElections,
                'open_categories' => $openCategoryCount,
                'last_vote_at' => $lastVote?->voted_at,
            ],
            'recentActivity' => $this->studentRecentActivity($user),
            'donationSummary' => [
                'total_donated' => $totalDonated,
                'campaigns_supported' => $fundraisersSupported,
                'donations_made' => $user->donations_count,
                'latest_title' => $lastDonation?->fundraiser?->title,
                'latest_amount' => $lastDonation ? (float) $lastDonation->amount : null,
                'latest_at' => $lastDonation?->donated_at,
                'history' => $recentDonations,
            ],
            'engagement' => $engagement,
            'achievements' => [
                'most_active_semester' => $totalActivities > 0
                    ? now()->format('F Y')
                    : null,
                'total_activities' => $totalActivities,
                'certificates_earned' => 0,
                'participation_level' => $participationLevel,
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{message: string, time: string}>
     */
    protected function studentRecentActivity($user): \Illuminate\Support\Collection
    {
        $items = collect();

        Vote::query()
            ->with(['election:id,title'])
            ->where('user_id', $user->id)
            ->orderByDesc('voted_at')
            ->limit(8)
            ->get()
            ->unique('election_id')
            ->take(3)
            ->each(function (Vote $vote) use ($items) {
                $items->push([
                    'message' => 'Voted in '.($vote->election?->title ?? 'an election'),
                    'time' => $vote->voted_at?->diffForHumans() ?? 'Recently',
                    'at' => $vote->voted_at,
                ]);
            });

        TalentEventEntry::query()
            ->with(['talentEvent:id,title'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(3)
            ->get()
            ->each(function (TalentEventEntry $entry) use ($items) {
                $items->push([
                    'message' => 'Joined '.($entry->talentEvent?->title ?? 'a talent competition'),
                    'time' => $entry->created_at?->diffForHumans() ?? 'Recently',
                    'at' => $entry->created_at,
                ]);
            });

        Donation::query()
            ->with(['fundraiser:id,title'])
            ->where('user_id', $user->id)
            ->orderByDesc('donated_at')
            ->limit(3)
            ->get()
            ->each(function (Donation $donation) use ($items) {
                $amount = number_format((float) $donation->amount, 2);
                $title = $donation->fundraiser?->title ?? 'a fundraiser';
                $items->push([
                    'message' => 'Donated ₱'.$amount.' to '.$title,
                    'time' => $donation->donated_at?->diffForHumans() ?? 'Recently',
                    'at' => $donation->donated_at,
                ]);
            });

        $user->passkeys()
            ->orderByDesc('created_at')
            ->limit(2)
            ->get()
            ->each(function ($passkey) use ($items) {
                $items->push([
                    'message' => 'Registered Passkey',
                    'time' => $passkey->created_at?->diffForHumans() ?? 'Recently',
                    'at' => $passkey->created_at,
                ]);
            });

        return $items
            ->sortByDesc(fn (array $item) => $item['at']?->getTimestamp() ?? 0)
            ->take(8)
            ->values()
            ->map(fn (array $item) => [
                'message' => $item['message'],
                'time' => $item['time'],
            ]);
    }

    public function eventShow(Request $request, Event $event): View
    {
        return view('student.events.show', [
            'user' => $request->user()->loadCount('passkeys'),
            'event' => $event,
        ]);
    }

    public function campaigns(Request $request): View
    {
        return view('student.campaigns.index', [
            'user' => $request->user()->loadCount('passkeys'),
            'campaigns' => $this->campaignService->paginatedPublished(),
        ]);
    }

    public function campaignShow(Request $request, Partylist $partylist): View
    {
        $this->campaignService->assertVisibleToStudents($partylist);

        $partylist->load([
            'elections',
            'approvedPosters',
        ]);

        $relevantElection = $this->campaignService->relevantElection($partylist);
        $campaignCandidates = $this->campaignService->candidatesFor($partylist, $relevantElection);

        return view('student.campaigns.show', [
            'user' => $request->user()->loadCount('passkeys'),
            'campaign' => $partylist,
            'relevantElection' => $relevantElection,
            'campaignCandidates' => $campaignCandidates,
            'buttonState' => $this->campaignService->buttonStateFor($partylist, $request->user()),
        ]);
    }

    public function voting(Request $request): View
    {
        $elections = Election::query()
            ->orderByDesc('voting_starts_at')
            ->paginate(10);

        return view('student.voting.index', [
            'user' => $request->user()->loadCount('passkeys'),
            'elections' => $elections,
            'electionService' => $this->electionService,
            'student' => $request->user(),
        ]);
    }

    public function votingShow(Request $request, Election $election): View|RedirectResponse
    {
        $student = $request->user();

        if (! $student->is_active || ! $student->canVote()) {
            return redirect()
                ->route('student.voting.index')
                ->with('error', 'Your account is not eligible to vote.');
        }

        if (! $this->electionService->canAccessBallot($election)) {
            return view('student.voting.unavailable', [
                'user' => $student->loadCount('passkeys'),
                'election' => $election,
                'message' => $this->electionService->ballotUnavailableMessage($election),
            ]);
        }

        $sessionKey = $this->ballotSubmittedSessionKey($election);
        $justSubmitted = (bool) $request->session()->pull($sessionKey);

        // Success / receipt UI is one-shot: only after a fresh submission flash.
        // Revisit or refresh without the flash must not re-show the success page.
        if ($election->hasStudentCompletedBallot($student) && ! $justSubmitted) {
            return redirect()
                ->route('student.voting.index')
                ->with('error', 'You have already submitted your vote for this election.');
        }

        $election->loadMissing([
            'categories',
            'activeCandidates',
            'activeCandidates.user',
        ]);

        // [election_category_id => candidate_id] for positions already voted.
        $existingVotes = $student->votes()
            ->where('election_id', $election->id)
            ->pluck('candidate_id', 'election_category_id');

        $votedCategoryIds = $existingVotes->keys()->map(fn ($id) => (int) $id)->all();
        $completed = $election->hasStudentCompletedBallot($student);

        $ballotReceipt = null;
        $submittedAt = null;

        if ($justSubmitted) {
            $ballotReceipt = BallotSubmission::query()
                ->where('user_id', $student->id)
                ->where('election_id', $election->id)
                ->first();

            $submittedAt = $ballotReceipt?->submitted_at
                ?? (($ts = $student->votes()->where('election_id', $election->id)->max('voted_at'))
                    ? \Illuminate\Support\Carbon::parse($ts)
                    : null);
        }

        return view('student.voting.show', [
            'user' => $student->loadCount('passkeys'),
            'election' => $election,
            'votedCategoryIds' => $votedCategoryIds,
            'existingVotes' => $existingVotes,
            'availability' => $this->electionService->votingAvailability($election, $student),
            'completed' => $completed,
            'justSubmitted' => $justSubmitted,
            'ballotReceipt' => $ballotReceipt,
            'submittedAt' => $submittedAt,
            'countdown' => $election->countdownSnapshot(),
        ]);
    }

    public function castVote(Request $request, Candidate $candidate): RedirectResponse
    {
        $candidate->loadMissing('election');

        return redirect()
            ->route('student.voting.show', $candidate->election)
            ->with('error', 'Please review and submit your complete ballot from the voting page. Individual vote submissions are no longer accepted.');
    }

    /**
     * Submit a full ballot (one candidate per position) atomically. Reuses the
     * existing per-category Vote::castBallot integrity logic and the unique
     * (user_id, election_category_id) index so one-student-one-vote is unchanged.
     */
    public function submitBallot(SubmitBallotRequest $request, Election $election): RedirectResponse
    {
        $student = $request->user();

        if (! $student->is_active || ! $student->canVote()) {
            return redirect()
                ->route('student.voting.index')
                ->with('error', 'Your account is not eligible to vote.');
        }

        if (! $this->electionService->canAccessBallot($election)) {
            return redirect()
                ->route('student.voting.index')
                ->with('error', 'This election is not currently open for voting.');
        }

        if ($election->hasStudentCompletedBallot($student)) {
            return redirect()
                ->route('student.voting.index')
                ->with('error', 'You have already submitted your vote for this election.');
        }

        $toCast = $request->candidatesToCast();

        if ($toCast === []) {
            return redirect()
                ->route('student.voting.show', $election)
                ->with('error', 'You must select a candidate for every position before submitting your ballot.');
        }

        try {
            $receipt = DB::transaction(function () use ($student, $toCast, $election, $request) {
                foreach ($toCast as $candidate) {
                    Vote::castBallot($student, $candidate);
                }

                $receipt = BallotSubmission::recordFor($student, $election);

                $this->notifications->ballotSubmitted($student, $election, $receipt->receipt_token);

                $this->audit->record(
                    $student,
                    'Student Cast Vote',
                    AuditActionType::Election,
                    targetType: 'election',
                    targetId: $election->id,
                    metadata: [
                        'account_id' => $student->account_id,
                        'election_title' => $election->title,
                        'receipt_number' => $receipt->receipt_token,
                    ],
                    request: $request,
                );

                return $receipt;
            });
        } catch (VoteIntegrityException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (\InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('student.voting.show', $election)
            ->with($this->ballotSubmittedSessionKey($election), true)
            ->with('ballot_submitted', true);
    }

    protected function ballotSubmittedSessionKey(Election $election): string
    {
        return 'ballot_submitted_'.$election->id;
    }

    public function talentVoting(Request $request): View
    {
        $student = $request->user()->loadCount('passkeys');
        $events = $this->talentService->paginatedPublishedEvents($student);

        return view('student.talent-voting.index', [
            'user' => $student,
            'events' => $events,
        ]);
    }

    public function talentVotingShow(Request $request, TalentEvent $talentEvent): View
    {
        $this->talentService->assertVisibleToStudents($talentEvent);

        $student = $request->user()->loadCount('passkeys');

        $talentEvent->load([
            'approvedEntries' => fn ($q) => $q->withCount('votes'),
        ]);

        $hasVoted = $this->talentService->hasVoted($student, $talentEvent);
        $votedEntryId = $hasVoted
            ? $this->talentService->votedEntryId($student, $talentEvent)
            : null;
        $canViewStandings = $this->talentService->canViewStandings($student, $talentEvent);

        $studentEntry = TalentEventEntry::query()
            ->where('talent_event_id', $talentEvent->id)
            ->where('user_id', $student->id)
            ->first();

        $heroActions = app(StudentTalentHeroActionResolver::class)->resolve(
            $talentEvent,
            $student,
            $hasVoted,
            $studentEntry,
        );

        return view('student.talent-voting.show', [
            'user' => $student,
            'talentEvent' => $talentEvent,
            'hasVoted' => $hasVoted,
            'votedEntryId' => $votedEntryId,
            'canViewStandings' => $canViewStandings,
            'studentEntry' => $studentEntry,
            'heroActions' => $heroActions,
        ]);
    }

    public function talentStandings(Request $request, TalentEvent $talentEvent): JsonResponse
    {
        $this->talentService->assertVisibleToStudents($talentEvent);

            abort_unless(
            $this->talentService->canViewStandings($request->user(), $talentEvent),
            403,
            'Standings are published after official results are released.'
        );

        return response()->json($this->talentService->standings($talentEvent));
    }

    public function castTalentVote(Request $request, TalentEventEntry $entry): RedirectResponse
    {
        try {
            TalentEventVote::castVote($request->user(), $entry);
        } catch (VoteIntegrityException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $entry->loadMissing('talentEvent');

        return redirect()
            ->route('student.talent-voting.show', $entry->talentEvent)
            ->with('success', 'Your vote has been recorded successfully.');
    }

    public function recordTalentView(Request $request, TalentEventEntry $entry): JsonResponse
    {
        $entry->loadMissing('talentEvent');
        $this->talentService->assertVisibleToStudents($entry->talentEvent);

        abort_unless($entry->isApproved(), 404);

        $entry->incrementViews();

        return response()->json(['views' => $entry->fresh()->view_count]);
    }

    public function fundraising(Request $request): View
    {
        $fundraisers = Fundraiser::query()
            ->visibleToStudents()
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('student.fundraising.index', [
            'user' => $request->user()->loadCount('passkeys'),
            'fundraisers' => $fundraisers,
        ]);
    }

    public function fundraiserShow(Request $request, Fundraiser $fundraiser): View
    {
        abort_if(
            in_array($fundraiser->status, [
                \App\Enums\FundraiserStatus::Draft,
                \App\Enums\FundraiserStatus::Archived,
                \App\Enums\FundraiserStatus::Cancelled,
            ], true)
            || $fundraiser->visibility === \App\Enums\FundraiserVisibility::Hidden,
            404
        );

        $fundraiser->loadMissing('donations');

        return view('student.fundraising.show', [
            'user' => $request->user()->loadCount('passkeys'),
            'fundraiser' => $fundraiser,
        ]);
    }

    public function donate(Request $request, Fundraiser $fundraiser): RedirectResponse
    {
        $min = $fundraiser->minimumDonationAmount();
        $max = $fundraiser->maximumDonationAmount();

        $amountRules = ['required', 'numeric', 'min:'.$min];
        if ($max !== null) {
            $amountRules[] = 'max:'.$max;
        }

        $validated = $request->validate([
            'amount' => $amountRules,
            'message' => ['nullable', 'string', 'max:255'],
            'is_anonymous' => ['nullable', 'boolean'],
        ]);

        try {
            Donation::record(
                donor: $request->user(),
                fundraiser: $fundraiser,
                amount: $validated['amount'],
                attributes: [
                    'message' => $validated['message'] ?? null,
                    'is_anonymous' => (bool) ($validated['is_anonymous'] ?? false),
                    'currency' => 'PHP',
                ],
            );
        } catch (DonationIntegrityException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $this->notifications->donationReceived(
            $fundraiser->title,
            (float) $validated['amount'],
            $request->user(),
            $request->user(),
        );

        return back()->with('success', 'Thank you! Your donation has been recorded.');
    }

    public function announcements(Request $request): View
    {
        $user = $request->user();

        $announcements = Announcement::query()
            ->published()
            ->visibleToUser($user)
            ->with('attachments')
            ->paginate(12);

        return view('student.announcements.index', [
            'user' => $user->loadCount('passkeys'),
            'announcements' => $announcements,
        ]);
    }

    public function announcementShow(Request $request, Announcement $announcement): View
    {
        abort_unless($announcement->isLive(), 404);
        abort_unless(
            $this->announcements->recipientQuery($announcement)->where('id', $request->user()->id)->exists(),
            403,
            'You do not have permission to view this announcement.',
        );

        $announcement->load('attachments');
        $this->announcements->recordView($announcement, $request->user());

        return view('student.announcements.show', [
            'user' => $request->user()->loadCount('passkeys'),
            'announcement' => $announcement,
            'preview' => false,
        ]);
    }

    public function downloadAnnouncementAttachment(Request $request, Announcement $announcement, AnnouncementAttachment $attachment): StreamedResponse
    {
        abort_unless($announcement->isLive(), 404);
        abort_unless($attachment->announcement_id === $announcement->id, 404);
        abort_unless(Storage::disk('public')->exists($attachment->path), 404);

        $attachment->increment('download_count');

        return Storage::disk('public')->download($attachment->path, $attachment->original_name);
    }

    public function notifications(Request $request): View
    {
        return $this->notificationIndexView(
            $request,
            'student.notifications.index',
            route('student.notifications.index'),
        );
    }

    public function notificationsFeed(Request $request): JsonResponse
    {
        return $this->feedJson($request);
    }

    public function candidateShow(Request $request, Candidate $candidate): View
    {
        $candidate->load(['election', 'category', 'user']);

        abort_unless($candidate->is_active, 404);

        $grade = $candidate->grade_level ?: $candidate->user?->grade_level;
        $section = $candidate->section ?: $candidate->user?->section;

        return view('student.candidates.show', [
            'user' => $request->user()->loadCount('passkeys'),
            'candidate' => $candidate,
            'photoUrl' => EventImageUrl::hasUploadedImage($candidate->photo_path)
                ? EventImageUrl::resolve($candidate->photo_path)
                : null,
            'grade' => $grade,
            'section' => $section,
        ]);
    }

    public function resultsIndex(Request $request): View
    {
        $events = $this->resultsService->listEvents();

        return view('student.results.index', [
            'user' => $request->user()->loadCount('passkeys'),
            'events' => $events,
            'hasEvents' => $this->resultsService->hasAnyEvents(),
            'hasCompletedEvents' => $this->resultsService->hasCompletedEvents(),
        ]);
    }

    public function resultsShowElection(Request $request, Election $election): View
    {
        abort_if(
            $election->status === \App\Enums\ElectionStatus::Draft
            && ! $this->resultsService->isElectionOfficial($election),
            404,
        );

        return view('student.results.show', [
            'user' => $request->user()->loadCount('passkeys'),
            'detail' => $this->resultsService->electionDetail($election),
        ]);
    }

    public function resultsShowTalent(Request $request, TalentEvent $talentEvent): View
    {
        $this->resultsService->assertVisibleTalentEvent($talentEvent);

        return view('student.results.show', [
            'user' => $request->user()->loadCount('passkeys'),
            'detail' => $this->resultsService->talentDetail($talentEvent),
        ]);
    }
}
