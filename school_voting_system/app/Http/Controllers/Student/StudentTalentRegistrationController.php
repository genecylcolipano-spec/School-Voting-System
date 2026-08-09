<?php

namespace App\Http\Controllers\Student;

use App\Enums\TalentEventStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\ConfirmTalentEntryRequest;
use App\Http\Requests\Student\SubmitTalentEntryRequest;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Services\Portal\PortalNotificationService;
use App\Services\Talent\StudentTalentRegistrationFlowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentTalentRegistrationController extends Controller
{
    public function __construct(
        protected PortalNotificationService $notifications,
        protected StudentTalentRegistrationFlowService $flow,
    ) {
    }

    public function index(): View
    {
        $events = TalentEvent::query()
            ->publishedToStudents()
            ->where('status', '!=', TalentEventStatus::Completed)
            ->where(function ($query) {
                $query->whereNotNull('registration_starts_at')
                    ->orWhereNotNull('registration_ends_at')
                    ->orWhereNotNull('event_date');
            })
            ->withCount([
                'entries as active_entries_count' => function ($query) {
                    $query->whereNotIn('status', [
                        TalentEventEntry::STATUS_WITHDRAWN,
                        TalentEventEntry::STATUS_DISQUALIFIED,
                        TalentEventEntry::STATUS_ARCHIVED,
                    ]);
                },
            ])
            ->orderByDesc('registration_ends_at')
            ->orderByDesc('event_date')
            ->get();

        $myEntries = TalentEventEntry::query()
            ->where('user_id', auth()->id())
            ->whereIn('talent_event_id', $events->pluck('id'))
            ->get()
            ->keyBy('talent_event_id');

        return view('student.talent-registration.index', [
            'events' => $events,
            'myEntries' => $myEntries,
            'flow' => $this->flow,
        ]);
    }

    public function show(Request $request, TalentEvent $talentEvent): View
    {
        abort_unless($talentEvent->isPublishedToStudents(), 404);

        $talentEvent->load(['judgingCriteria', 'creator']);

        $action = $this->flow->registrationAction($talentEvent, $request->user());

        return view('student.talent-registration.show', [
            'talentEvent' => $talentEvent,
            'action' => $action,
            'registeredCount' => $action['registered_count'],
            'remainingSlots' => $action['remaining_slots'],
        ]);
    }

    public function register(Request $request, TalentEvent $talentEvent): View|RedirectResponse
    {
        abort_unless($talentEvent->isPublishedToStudents(), 404);

        $existing = TalentEventEntry::query()
            ->where('talent_event_id', $talentEvent->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            return redirect()->route('student.talent-registration.entry.show', $existing);
        }

        $this->flow->assertCanAccessRegisterForm($talentEvent, $request->user());

        $draft = $this->flow->getDraft($talentEvent, $request->user());

        return view('student.talent-registration.create', [
            'talentEvent' => $talentEvent,
            'draft' => $draft,
        ]);
    }

    /**
     * Legacy URL: /talent-registration/{slug} used to open the form.
     * Keep a named redirect target for older bookmarks.
     */
    public function create(Request $request, TalentEvent $talentEvent): RedirectResponse
    {
        $existing = TalentEventEntry::query()
            ->where('talent_event_id', $talentEvent->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            return redirect()->route('student.talent-registration.entry.show', $existing);
        }

        return redirect()->route('student.talent-registration.show', $talentEvent);
    }

    public function prepareReview(SubmitTalentEntryRequest $request, TalentEvent $talentEvent): RedirectResponse
    {
        $this->flow->assertCanAccessRegisterForm($talentEvent, $request->user());

        $fields = collect($request->validated())
            ->except(['photo', 'thumbnail', 'video'])
            ->all();

        $this->flow->stashDraft(
            $talentEvent,
            $request->user(),
            $fields,
            [
                'photo' => $request->file('photo'),
                'thumbnail' => $request->file('thumbnail'),
                'video' => $request->file('video'),
            ],
        );

        return redirect()->route('student.talent-registration.review', $talentEvent);
    }

    public function review(Request $request, TalentEvent $talentEvent): View|RedirectResponse
    {
        abort_unless($talentEvent->isPublishedToStudents(), 404);

        $existing = TalentEventEntry::query()
            ->where('talent_event_id', $talentEvent->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            return redirect()->route('student.talent-registration.entry.show', $existing);
        }

        $draft = $this->flow->getDraft($talentEvent, $request->user());

        if (! $draft) {
            return redirect()
                ->route('student.talent-registration.register', $talentEvent)
                ->with('error', 'Please complete the registration form before reviewing your entry.');
        }

        $action = $this->flow->registrationAction($talentEvent, $request->user());

        if (! $action['can_register']) {
            $this->flow->clearDraft($talentEvent, $request->user());

            return redirect()
                ->route('student.talent-registration.show', $talentEvent)
                ->with('error', $action['label']);
        }

        return view('student.talent-registration.review', [
            'talentEvent' => $talentEvent,
            'draft' => $draft,
        ]);
    }

    public function store(ConfirmTalentEntryRequest $request, TalentEvent $talentEvent): RedirectResponse
    {
        $user = $request->user();
        $draft = $this->flow->getDraft($talentEvent, $user);

        if (! $draft) {
            return redirect()
                ->route('student.talent-registration.register', $talentEvent)
                ->with('error', 'Please complete the registration form and review your entry before submitting.');
        }

        $entry = DB::transaction(function () use ($talentEvent, $user, $draft) {
            $paths = $this->flow->promoteDraftFiles($draft);
            $fields = $draft['fields'] ?? [];

            return TalentEventEntry::query()->create([
                'talent_event_id' => $talentEvent->id,
                'user_id' => $user->id,
                'entry_number' => $this->flow->generateEntryNumber($talentEvent),
                'student_id_number' => $fields['student_id_number'],
                'display_name' => $fields['display_name'],
                'grade_level' => $fields['grade_level'],
                'section' => $fields['section'],
                'course_strand' => $fields['course_strand'] ?? null,
                'talent_category' => $fields['talent_category'],
                'performance_title' => $fields['performance_title'],
                'profile_summary' => $fields['profile_summary'] ?? null,
                'performance_description' => $fields['performance_description'],
                'social_media' => $fields['social_media'] ?? null,
                'photo_path' => $paths['photo_path'],
                'thumbnail_path' => $paths['thumbnail_path'],
                'video_path' => $paths['video_path'],
                'video_url' => $fields['video_url'] ?? null,
                'status' => TalentEventEntry::STATUS_PENDING,
                'source' => TalentEventEntry::SOURCE_SELF,
                'submitted_at' => now(),
            ]);
        });

        $this->flow->clearDraft($talentEvent, $user);
        $this->notifications->talentSubmissionReceived($entry, $user);

        session([
            $this->flow->successSessionKey($talentEvent) => [
                'entry_id' => $entry->id,
                'submitted_at' => now()->toIso8601String(),
            ],
        ]);

        return redirect()->route('student.talent-registration.success', $talentEvent);
    }

    public function success(Request $request, TalentEvent $talentEvent): View|RedirectResponse
    {
        $payload = session($this->flow->successSessionKey($talentEvent));

        if (! is_array($payload) || empty($payload['entry_id'])) {
            return redirect()->route('student.talent-registration.my-entries');
        }

        $entry = TalentEventEntry::query()
            ->where('id', $payload['entry_id'])
            ->where('user_id', $request->user()->id)
            ->where('talent_event_id', $talentEvent->id)
            ->first();

        if (! $entry) {
            session()->forget($this->flow->successSessionKey($talentEvent));

            return redirect()->route('student.talent-registration.my-entries');
        }

        // One-time access: clear after rendering so refresh/bookmark cannot reopen.
        session()->forget($this->flow->successSessionKey($talentEvent));

        return view('student.talent-registration.success', [
            'talentEvent' => $talentEvent,
            'entry' => $entry,
        ]);
    }

    public function myEntries(Request $request): View
    {
        $entries = TalentEventEntry::query()
            ->with(['talentEvent'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('submitted_at')
            ->orderByDesc('created_at')
            ->get();

        return view('student.talent-registration.my-entries', [
            'entries' => $entries,
            'flow' => $this->flow,
        ]);
    }

    public function showEntry(Request $request, TalentEventEntry $entry): View
    {
        abort_unless((int) $entry->user_id === (int) $request->user()->id, 403);

        $entry->load('talentEvent');

        return view('student.talent-registration.entry', [
            'talentEvent' => $entry->talentEvent,
            'entry' => $entry,
            'flow' => $this->flow,
        ]);
    }

    public function downloadConfirmation(Request $request, TalentEventEntry $entry): StreamedResponse
    {
        abort_unless((int) $entry->user_id === (int) $request->user()->id, 403);

        $entry->load('talentEvent');
        $event = $entry->talentEvent;
        $filename = 'talent-entry-'.$entry->entry_number.'-confirmation.txt';

        $lines = [
            'TALENT COMPETITION ENTRY CONFIRMATION',
            str_repeat('=', 42),
            'Competition: '.$event->title,
            'Entry Number: '.($entry->entry_number ?: '—'),
            'Participant: '.$entry->display_name,
            'Student ID: '.$entry->student_id_number,
            'Category: '.($entry->talent_category?->label() ?? '—'),
            'Performance Title: '.$entry->performance_title,
            'Submitted: '.optional($entry->submitted_at)->format('M d, Y g:i A'),
            'Status: '.$entry->statusLabel(),
            'Review Status: '.$this->flow->reviewStatusLabel($entry),
            '',
            'This confirmation was generated from the student portal.',
        ];

        return response()->streamDownload(function () use ($lines) {
            echo implode("\r\n", $lines);
        }, $filename, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
