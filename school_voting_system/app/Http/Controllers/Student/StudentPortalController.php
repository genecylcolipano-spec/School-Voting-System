<?php

namespace App\Http\Controllers\Student;

use App\Enums\AuditActionType;
use App\Enums\DonationPaymentMethod;
use App\Exceptions\DonationIntegrityException;
use App\Exceptions\PayMongoException;
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
use App\Services\Payments\DonationCheckoutService;
use App\Services\Student\StudentResultsService;
use App\Services\Student\StudentStatisticsService;
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
        protected DonationCheckoutService $donationCheckout,
        protected StudentStatisticsService $statistics,
    ) {}

    public function events(Request $request): View
    {
        Event::markOverdueAsCompleted();

        $user = $request->user()->loadCount('passkeys');

        $events = Event::query()
            ->campusListing()
            ->paginate(12);

        return view('student.events.index', [
            'user' => $user,
            'notificationsCount' => $this->notifications->unreadCountFor($user),
            'events' => $events,
        ]);
    }

    public function statistics(Request $request): View
    {
        $user = $request->user()->loadCount('passkeys');
        $stats = $this->statistics->forStudent($user);

        return view('student.statistics.index', [
            'user' => $user,
            'notificationsCount' => $this->notifications->unreadCountFor($user),
            ...$stats,
        ]);
    }

    public function eventShow(Request $request, Event $event): View
    {
        abort_unless($event->isVisibleToCampus(), 404);
        Event::markOverdueAsCompleted();
        $event->refresh();

        $user = $request->user()->loadCount('passkeys');

        return view('student.events.show', [
            'user' => $user,
            'notificationsCount' => $this->notifications->unreadCountFor($user),
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
            ->visibleToCampus()
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
        abort_unless($election->isVisibleToCampus(), 404);

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
        abort_unless($election->isVisibleToCampus(), 404);

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
            'watchedEntryIds' => $this->talentService->watchedEntryIds($student, $talentEvent),
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

        $this->talentService->recordWatch($request->user(), $entry);
        $entry->incrementViews();

        return response()->json([
            'views' => $entry->fresh()->view_count,
            'watched' => true,
            'entry_id' => $entry->id,
        ]);
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
            'paymentMethods' => $fundraiser->acceptedPaymentMethods(),
            'paymongoConfigured' => $this->donationCheckout->isOnlinePaymentsConfigured(),
            'onlineMinAmount' => $this->donationCheckout->onlineMinimumAmount(),
        ]);
    }

    public function donate(Request $request, Fundraiser $fundraiser): RedirectResponse
    {
        $min = $fundraiser->minimumDonationAmount();
        $max = $fundraiser->maximumDonationAmount();
        $accepted = array_map(
            fn (DonationPaymentMethod $method) => $method->value,
            $fundraiser->acceptedPaymentMethods(),
        );

        $amountRules = ['required', 'numeric', 'min:'.$min];
        if ($max !== null) {
            $amountRules[] = 'max:'.$max;
        }

        $validated = $request->validate([
            'amount' => $amountRules,
            'message' => ['nullable', 'string', 'max:255'],
            'is_anonymous' => ['nullable', 'boolean'],
            'payment_method' => ['required', 'in:'.implode(',', $accepted)],
        ]);

        $method = DonationPaymentMethod::from($validated['payment_method']);

        try {
            $result = $this->donationCheckout->start(
                donor: $request->user(),
                fundraiser: $fundraiser,
                amount: $validated['amount'],
                method: $method,
                message: $validated['message'] ?? null,
                anonymous: (bool) ($validated['is_anonymous'] ?? false),
            );
        } catch (DonationIntegrityException|PayMongoException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        if (filled($result['checkout_url'])) {
            return redirect()->away($result['checkout_url']);
        }

        return back()->with('success', $result['message']);
    }

    public function donateReturn(Request $request, Fundraiser $fundraiser): RedirectResponse
    {
        $donation = $this->ownedDonation($request, $fundraiser, $request->query('donation'));

        if ($donation && $donation->payment_method?->isOnline()) {
            $this->donationCheckout->syncFromPayMongo($donation);
            $donation->refresh();
        }

        if ($donation?->isPaid()) {
            return redirect()
                ->route('student.fundraising.show', $fundraiser)
                ->with('success', 'Thank you! Your donation of ₱'.number_format((float) $donation->amount, 2).' was received.');
        }

        return redirect()
            ->route('student.fundraising.show', $fundraiser)
            ->with('success', 'If your payment went through, it will appear here after PayMongo confirms it. This can take a few seconds.');
    }

    public function donateCancel(Request $request, Fundraiser $fundraiser): RedirectResponse
    {
        $donation = $this->ownedDonation($request, $fundraiser, $request->query('donation'));

        if ($donation?->isPending()) {
            $donation->markCancelled();
        }

        return redirect()
            ->route('student.fundraising.show', $fundraiser)
            ->with('error', 'Payment was cancelled. No amount was charged.');
    }

    protected function ownedDonation(Request $request, Fundraiser $fundraiser, mixed $donationId): ?Donation
    {
        if (! is_numeric($donationId)) {
            return null;
        }

        return Donation::query()
            ->whereKey((int) $donationId)
            ->where('fundraiser_id', $fundraiser->id)
            ->where('user_id', $request->user()->id)
            ->first();
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
            $this->announcements->userCanView($announcement, $request->user()),
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
        abort_unless(
            $this->announcements->userCanView($announcement, $request->user()),
            403,
            'You do not have permission to view this announcement.',
        );
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
        $user = $request->user()->loadCount('passkeys');
        $events = $this->resultsService->listEvents($user);

        return view('student.results.index', [
            'user' => $user,
            'notificationsCount' => $this->notifications->unreadCountFor($user),
            'events' => $events,
            'hasEvents' => $this->resultsService->hasAnyEvents(),
            'hasCompletedEvents' => $this->resultsService->hasCompletedEvents(),
        ]);
    }

    public function resultsShowElection(Request $request, Election $election): View
    {
        $this->resultsService->assertVisibleElection($election);

        $user = $request->user()->loadCount('passkeys');

        return view('student.results.show', [
            'user' => $user,
            'notificationsCount' => $this->notifications->unreadCountFor($user),
            'detail' => $this->resultsService->electionDetail($election),
        ]);
    }

    public function resultsShowTalent(Request $request, TalentEvent $talentEvent): View
    {
        $this->resultsService->assertVisibleTalentEvent($talentEvent);

        $user = $request->user()->loadCount('passkeys');

        return view('student.results.show', [
            'user' => $user,
            'notificationsCount' => $this->notifications->unreadCountFor($user),
            'detail' => $this->resultsService->talentDetail($talentEvent),
        ]);
    }
}
