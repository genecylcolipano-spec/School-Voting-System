<?php

namespace App\Http\Controllers\Faculty;

use App\Exceptions\JudgingIntegrityException;
use App\Http\Controllers\Controller;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\TalentEventJudge;
use App\Models\TalentJudgeScoreSheet;
use App\Models\User;
use App\Services\Talent\TalentJudgingService;
use App\Support\AdminPortal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FacultyJudgingController extends Controller
{
    public function __construct(
        protected TalentJudgingService $judging,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user()->loadCount('passkeys');
        $this->judging->assertHasActiveAssignment($user);
        $phase = $request->string('filter')->toString() === 'past' ? 'past' : 'current';

        $competitions = $this->judging->assignedCompetitionsQuery($user, $phase)
            ->withCount([
                'entries as approved_entries_count' => fn ($q) => $q->where('status', TalentEventEntry::STATUS_APPROVED),
            ])
            ->paginate(12)
            ->withQueryString();

        $assignments = TalentEventJudge::query()
            ->active()
            ->where('user_id', $user->id)
            ->whereIn('talent_event_id', $competitions->getCollection()->pluck('id'))
            ->get()
            ->keyBy('talent_event_id');

        $progress = [];
        foreach ($competitions as $competition) {
            $progress[$competition->id] = $this->judging->progressFor($user, $competition);
        }

        return view('faculty.judging.index', [
            'user' => $user,
            'notificationsCount' => AdminPortal::notificationCount($user),
            'competitions' => $competitions,
            'assignments' => $assignments,
            'progress' => $progress,
            'phase' => $phase,
            'currentCount' => $this->judging->assignedCompetitionsQuery($user, 'current')->count(),
            'pastCount' => $this->judging->assignedCompetitionsQuery($user, 'past')->count(),
        ]);
    }

    public function performances(Request $request): View
    {
        $user = $request->user()->loadCount('passkeys');
        $this->judging->assertHasActiveAssignment($user);

        return view('faculty.judging.performances', [
            'user' => $user,
            'notificationsCount' => AdminPortal::notificationCount($user),
            'rows' => $this->judging->currentPerformanceGroupsFor($user),
        ]);
    }

    public function submitted(Request $request): View
    {
        $user = $request->user()->loadCount('passkeys');
        $this->judging->assertHasActiveAssignment($user);

        $summaries = $this->judging->submittedSummariesFor($user);

        $sheets = $this->judging->submittedSheetsFor($user);

        return view('faculty.judging.submitted', [
            'user' => $user,
            'notificationsCount' => AdminPortal::notificationCount($user),
            'summaries' => $summaries,
            'sheets' => $sheets,
        ]);
    }

    public function show(Request $request, TalentEvent $talentEvent): View
    {
        $user = $request->user()->loadCount('passkeys');
        $this->judging->assertAssigned($user, $talentEvent);

        $entries = $this->judging->judgeableEntries($talentEvent);
        $sheets = TalentJudgeScoreSheet::query()
            ->where('talent_event_id', $talentEvent->id)
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('talent_event_entry_id');

        return view('faculty.judging.show', [
            'user' => $user,
            'notificationsCount' => AdminPortal::notificationCount($user),
            'competition' => $talentEvent,
            'entries' => $entries,
            'sheets' => $sheets,
            'progress' => $this->judging->progressFor($user, $talentEvent),
            'acceptingScores' => $talentEvent->isAcceptingJudgeScores(),
        ]);
    }

    public function scoreForm(Request $request, TalentEvent $talentEvent, TalentEventEntry $entry): View
    {
        $user = $request->user()->loadCount('passkeys');
        $this->assertJudgeableEntry($user, $talentEvent, $entry);

        $this->judging->ensureDefaultCriteria($talentEvent);
        $criteria = $talentEvent->judgingCriteria()->orderBy('sort_order')->get();
        $sheet = $this->judging->scoreSheetFor($user, $talentEvent, $entry);
        $existingScores = $sheet?->criterionScores->pluck('points', 'criterion_id') ?? collect();

        return view('faculty.judging.score', [
            'user' => $user,
            'notificationsCount' => AdminPortal::notificationCount($user),
            'competition' => $talentEvent,
            'entry' => $entry,
            'criteria' => $criteria,
            'sheet' => $sheet,
            'existingScores' => $existingScores,
            'acceptingScores' => $talentEvent->isAcceptingJudgeScores(),
            'locked' => $sheet?->isLocked() ?? false,
            'from' => $request->string('from')->toString(),
            'backUrl' => $this->entryBackUrl($request, $talentEvent),
            'backLabel' => $this->entryBackLabel($request, $talentEvent),
        ]);
    }

    public function profile(Request $request, TalentEvent $talentEvent, TalentEventEntry $entry): View
    {
        $user = $request->user()->loadCount('passkeys');
        $this->assertJudgeableEntry($user, $talentEvent, $entry);

        $sheet = $this->judging->scoreSheetFor($user, $talentEvent, $entry);

        return view('faculty.judging.profile', [
            'user' => $user,
            'notificationsCount' => AdminPortal::notificationCount($user),
            'competition' => $talentEvent,
            'entry' => $entry,
            'sheet' => $sheet,
            'from' => $request->string('from')->toString(),
            'backUrl' => $this->entryBackUrl($request, $talentEvent),
            'backLabel' => $this->entryBackLabel($request, $talentEvent),
        ]);
    }

    public function saveDraft(Request $request, TalentEvent $talentEvent, TalentEventEntry $entry): RedirectResponse
    {
        return $this->persist($request, $talentEvent, $entry, submit: false);
    }

    public function submit(Request $request, TalentEvent $talentEvent, TalentEventEntry $entry): RedirectResponse
    {
        return $this->persist($request, $talentEvent, $entry, submit: true);
    }

    protected function persist(
        Request $request,
        TalentEvent $talentEvent,
        TalentEventEntry $entry,
        bool $submit,
    ): RedirectResponse {
        $validated = $request->validate([
            'scores' => ['required', 'array'],
            'scores.*' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            if ($submit) {
                $this->judging->submitScores(
                    $request->user(),
                    $talentEvent,
                    $entry,
                    $validated['scores'],
                    $validated['notes'] ?? null,
                );
                $message = 'Scores submitted successfully.';
            } else {
                $this->judging->saveDraft(
                    $request->user(),
                    $talentEvent,
                    $entry,
                    $validated['scores'],
                    $validated['notes'] ?? null,
                );
                $message = 'Draft scores saved.';
            }
        } catch (JudgingIntegrityException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('faculty.judging.show', $talentEvent)
            ->with('success', $message);
    }

    protected function assertJudgeableEntry(User $user, TalentEvent $talentEvent, TalentEventEntry $entry): void
    {
        $this->judging->assertAssigned($user, $talentEvent);
        abort_unless((int) $entry->talent_event_id === (int) $talentEvent->id, 404);
        abort_unless($entry->isApproved(), 404);
    }

    protected function entryBackUrl(Request $request, TalentEvent $talentEvent): string
    {
        return match ($request->string('from')->toString()) {
            'performances' => route('faculty.judging.performances'),
            'submitted' => route('faculty.judging.submitted'),
            default => route('faculty.judging.show', $talentEvent),
        };
    }

    protected function entryBackLabel(Request $request, TalentEvent $talentEvent): string
    {
        return match ($request->string('from')->toString()) {
            'performances' => 'Judge Performances',
            'submitted' => 'Submitted Scores',
            default => $talentEvent->title,
        };
    }
}
