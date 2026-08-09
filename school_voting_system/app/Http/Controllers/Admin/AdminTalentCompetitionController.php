<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditActionType;
use App\Enums\TalentCategory;
use App\Enums\TalentEventStatus;
use App\Enums\TalentEventType;
use App\Enums\TalentRankingMethod;
use App\Enums\TalentRegistrationMethod;
use App\Enums\TalentSubmissionMethod;
use App\Enums\TalentVotingMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TalentCompetition\DeleteTalentCompetitionRequest;
use App\Http\Requests\Admin\TalentCompetition\StoreTalentCompetitionRequest;
use App\Http\Requests\Admin\TalentCompetition\UpdateTalentCompetitionRequest;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Services\Admin\AdminScopeService;
use App\Services\Media\ImageCompressionService;
use App\Services\Portal\AnnouncementService;
use App\Services\Portal\PortalNotificationService;
use App\Services\SuperAdmin\AuditLogService;
use App\Services\Talent\TalentEventPublishingService;
use App\Support\SlugGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminTalentCompetitionController extends Controller
{
    public function __construct(
        protected AdminScopeService $scope,
        protected AuditLogService $audit,
        protected TalentEventPublishingService $publishing,
        protected ImageCompressionService $images,
        protected PortalNotificationService $notifications,
        protected AnnouncementService $announcements,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user()->load(['staffRole', 'passkeys']);
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        $category = (string) $request->query('category', '');
        $sort = (string) $request->query('sort', 'newest');

        $eventIds = $user->isSuperAdmin()
            ? TalentEvent::query()->pluck('id')
            : $this->scope->talentEvents($user)->pluck('id');

        $query = TalentEvent::query()
            ->whereIn('id', $eventIds->isEmpty() ? [-1] : $eventIds)
            ->withCount([
                'entries',
                'votes',
                'entries as pending_entries_count' => fn ($q) => $q->where('status', TalentEventEntry::STATUS_PENDING),
                'entries as approved_entries_count' => fn ($q) => $q->where('status', TalentEventEntry::STATUS_APPROVED),
                'entries as rejected_entries_count' => fn ($q) => $q->where('status', TalentEventEntry::STATUS_REJECTED),
            ])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('competition_code', 'like', "%{$search}%")
                        ->orWhere('venue', 'like', "%{$search}%")
                        ->orWhere('organizer', 'like', "%{$search}%");
                });
            })
            ->when($category !== '', fn ($q) => $q->where('talent_category', $category));

        $events = $query->get();

        if ($status !== '') {
            $events = $events->filter(function (TalentEvent $event) use ($status) {
                return $event->currentStatusKey() === $status
                    || $event->status?->value === $status;
            })->values();
        }

        $events = match ($sort) {
            'oldest' => $events->sortBy('created_at')->values(),
            'title' => $events->sortBy('title')->values(),
            'votes' => $events->sortByDesc('votes_count')->values(),
            'participants' => $events->sortByDesc('entries_count')->values(),
            default => $events->sortByDesc('created_at')->values(),
        };

        $page = max(1, (int) $request->query('page', 1));
        $perPage = 10;
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $events->forPage($page, $perPage)->values(),
            $events->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.talent-competition.index', [
            'user' => $user,
            'notificationsCount' => 0,
            'assignedRole' => $user->staffRole?->name ?? 'Operations Admin',
            'talentEvents' => $paginator,
            'filters' => [
                'q' => $search,
                'status' => $status,
                'category' => $category,
                'sort' => $sort,
            ],
            'categories' => TalentCategory::cases(),
            'canManageTalentEvents' => $this->scope->canCreateTalentEvents($user),
            'canViewRealtimeTalentCounts' => $this->scope->canViewRealtimeTalentCounts($user),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($this->scope->canCreateTalentEvents($request->user()), 403);

        $user = $request->user()->load(['staffRole', 'passkeys']);

        return view('admin.talent-competition.create', [
            'user' => $user,
            'notificationsCount' => 0,
            'assignedRole' => $user->staffRole?->name ?? 'Operations Admin',
            'types' => TalentEventType::cases(),
            'categories' => TalentCategory::cases(),
            'votingMethods' => TalentVotingMethod::cases(),
            'registrationMethods' => TalentRegistrationMethod::cases(),
            'submissionMethods' => TalentSubmissionMethod::cases(),
            'rankingMethods' => TalentRankingMethod::cases(),
            'election' => $this->scope->assignedElection($user),
            'talentEvent' => null,
        ]);
    }

    public function store(StoreTalentCompetitionRequest $request): RedirectResponse
    {
        $election = $this->scope->assignedElection($request->user());
        $validated = $request->validated();

        $slug = SlugGenerator::unique($validated['title'], TalentEvent::class);

        $event = TalentEvent::query()->create([
            'election_id' => $election->id,
            'title' => $validated['title'],
            'slug' => $slug,
            'type' => $validated['type'],
            ...$request->competitionSettings(),
            'description' => $validated['description'] ?? null,
            'image_path' => $this->storeTalentEventImage($request->file('image')),
            'image_variants' => $this->lastStoredImageVariants,
            'thumbnail_path' => $request->hasFile('thumbnail')
                ? $this->storeTalentEventImage($request->file('thumbnail'), 'talent-events/thumbnails', false)
                : ($this->lastStoredImageVariants['thumb_path'] ?? null),
            'poster_path' => $this->storeTalentEventImage($request->file('poster'), 'talent-events/posters', false),
            'event_date' => $validated['event_date'],
            'venue' => $validated['venue'],
            'status' => TalentEventStatus::Scheduled,
            'voting_starts_at' => $validated['voting_starts_at'],
            'voting_ends_at' => $validated['voting_ends_at'],
            'published_to_students' => false,
            'created_by' => $request->user()->id,
        ]);

        $participants = $validated['participants'] ?? [];
        if (count($participants) > 0) {
            $this->syncParticipants($event, $participants, $request->user()->id);
            $this->publishing->publishIfReady($event->fresh(), $request->user());
        }

        $this->audit->record(
            $request->user(),
            "Created talent event: {$event->title}",
            AuditActionType::Election,
            targetType: 'talent_event',
            targetId: $event->id,
        );

        $this->notifications->talentCreated($event->fresh(), $request->user());

        return redirect()
            ->route('admin.talent-competition.show', $event)
            ->with('success', 'Talent competition created successfully.');
    }

    public function show(Request $request, TalentEvent $talentEvent): View
    {
        $this->scope->assertTalentEventInScope($request->user(), $talentEvent);

        $user = $request->user()->load(['staffRole', 'passkeys']);
        $talentEvent->loadCount([
            'entries',
            'votes',
            'entries as pending_entries_count' => fn ($q) => $q->where('status', TalentEventEntry::STATUS_PENDING),
            'entries as approved_entries_count' => fn ($q) => $q->where('status', TalentEventEntry::STATUS_APPROVED),
            'entries as rejected_entries_count' => fn ($q) => $q->where('status', TalentEventEntry::STATUS_REJECTED),
        ]);

        return view('admin.talent-competition.show', [
            'user' => $user,
            'notificationsCount' => 0,
            'assignedRole' => $user->staffRole?->name ?? 'Operations Admin',
            'talentEvent' => $talentEvent,
            'canManageTalentEvents' => $this->scope->canCreateTalentEvents($user),
            'canPublishResults' => $this->scope->canPublishTalentResults($user),
        ]);
    }

    public function edit(Request $request, TalentEvent $talentEvent): View
    {
        abort_unless($this->scope->canCreateTalentEvents($request->user()), 403);
        $this->scope->assertTalentEventInScope($request->user(), $talentEvent);

        $user = $request->user()->load(['staffRole', 'passkeys']);

        return view('admin.talent-competition.edit', [
            'user' => $user,
            'notificationsCount' => 0,
            'assignedRole' => $user->staffRole?->name ?? 'Operations Admin',
            'types' => TalentEventType::cases(),
            'categories' => TalentCategory::cases(),
            'votingMethods' => TalentVotingMethod::cases(),
            'registrationMethods' => TalentRegistrationMethod::cases(),
            'submissionMethods' => TalentSubmissionMethod::cases(),
            'rankingMethods' => TalentRankingMethod::cases(),
            'election' => $this->scope->assignedElection($user),
            'talentEvent' => $talentEvent,
        ]);
    }

    public function settings(Request $request, TalentEvent $talentEvent): View
    {
        abort_unless($this->scope->canCreateTalentEvents($request->user()), 403);
        $this->scope->assertTalentEventInScope($request->user(), $talentEvent);

        $user = $request->user()->load(['staffRole', 'passkeys']);

        return view('admin.talent-competition.settings', [
            'user' => $user,
            'notificationsCount' => 0,
            'assignedRole' => $user->staffRole?->name ?? 'Operations Admin',
            'talentEvent' => $talentEvent,
            'registrationMethods' => TalentRegistrationMethod::cases(),
            'submissionMethods' => TalentSubmissionMethod::cases(),
            'votingMethods' => TalentVotingMethod::cases(),
            'rankingMethods' => TalentRankingMethod::cases(),
        ]);
    }

    public function updateSettings(Request $request, TalentEvent $talentEvent): RedirectResponse
    {
        abort_unless($this->scope->canCreateTalentEvents($request->user()), 403);
        $this->scope->assertTalentEventInScope($request->user(), $talentEvent);

        $validated = $request->validate([
            'registration_method' => ['required', \Illuminate\Validation\Rule::enum(TalentRegistrationMethod::class)],
            'submission_method' => ['required', \Illuminate\Validation\Rule::enum(TalentSubmissionMethod::class)],
            'voting_method' => ['required', \Illuminate\Validation\Rule::enum(TalentVotingMethod::class)],
            'ranking_method' => ['required', \Illuminate\Validation\Rule::enum(TalentRankingMethod::class)],
            'published_to_students' => ['nullable', 'boolean'],
            'auto_status_updates' => ['nullable', 'boolean'],
            'judge_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'student_vote_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $method = TalentVotingMethod::from($validated['voting_method']);

        $talentEvent->forceFill([
            'registration_method' => $validated['registration_method'],
            'submission_method' => $validated['submission_method'],
            'voting_method' => $validated['voting_method'],
            'ranking_method' => $validated['ranking_method'],
            'published_to_students' => $request->boolean('published_to_students'),
            'published_at' => $request->boolean('published_to_students')
                ? ($talentEvent->published_at ?? now())
                : $talentEvent->published_at,
            'auto_status_updates' => $request->boolean('auto_status_updates', true),
            'judge_percentage' => $method->requiresHybridPercentages() ? (int) ($validated['judge_percentage'] ?? 70) : null,
            'student_vote_percentage' => $method->requiresHybridPercentages() ? (int) ($validated['student_vote_percentage'] ?? 30) : null,
        ])->save();

        $this->audit->record(
            $request->user(),
            "Updated competition settings: {$talentEvent->title}",
            AuditActionType::Election,
            targetType: 'talent_event',
            targetId: $talentEvent->id,
        );

        return redirect()
            ->route('admin.talent-competition.settings', $talentEvent)
            ->with('success', 'Competition settings saved.');
    }

    public function update(UpdateTalentCompetitionRequest $request, TalentEvent $talentEvent): RedirectResponse
    {
        $validated = $request->validated();

        $imagePath = $talentEvent->image_path;
        $imageVariants = $talentEvent->image_variants;
        $thumbnailPath = $talentEvent->thumbnail_path;
        $posterPath = $talentEvent->poster_path;

        if ($request->hasFile('image')) {
            $this->deleteTalentEventImage($talentEvent);
            $imagePath = $this->storeTalentEventImage($request->file('image'));
            $imageVariants = $this->lastStoredImageVariants;
            if (! $request->hasFile('thumbnail') && empty($thumbnailPath) && ! empty($imageVariants['thumb_path'])) {
                $thumbnailPath = $imageVariants['thumb_path'];
            }
        }

        if ($request->hasFile('thumbnail')) {
            $this->deleteTalentEventThumbnail($talentEvent);
            $thumbnailPath = $this->storeTalentEventImage($request->file('thumbnail'), 'talent-events/thumbnails', false);
        }

        if ($request->hasFile('poster')) {
            $this->deleteTalentEventPoster($talentEvent);
            $posterPath = $this->storeTalentEventImage($request->file('poster'), 'talent-events/posters', false);
        }

        $talentEvent->forceFill([
            'title' => $validated['title'],
            'slug' => $talentEvent->title !== $validated['title']
                ? SlugGenerator::unique($validated['title'], TalentEvent::class, $talentEvent->id)
                : $talentEvent->slug,
            'type' => $validated['type'],
            ...$request->competitionSettings(),
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'image_variants' => $imageVariants,
            'thumbnail_path' => $thumbnailPath,
            'poster_path' => $posterPath,
            'event_date' => $validated['event_date'],
            'venue' => $validated['venue'],
            'voting_starts_at' => $validated['voting_starts_at'],
            'voting_ends_at' => $validated['voting_ends_at'],
        ])->save();

        // Only sync participants if they were submitted (legacy form support).
        if (array_key_exists('participants', $validated) && is_array($validated['participants']) && count($validated['participants']) > 0) {
            $this->syncParticipants($talentEvent, $validated['participants'], $request->user()->id);
            $this->publishing->publishIfReady($talentEvent->fresh(), $request->user());
        }

        $this->audit->record(
            $request->user(),
            "Updated talent event: {$talentEvent->title}",
            AuditActionType::Election,
            targetType: 'talent_event',
            targetId: $talentEvent->id,
        );

        $this->notifications->talentUpdated($talentEvent->fresh(), $request->user());

        return redirect()
            ->route('admin.talent-competition.show', $talentEvent)
            ->with('success', 'Talent competition updated successfully.');
    }

    public function duplicate(Request $request, TalentEvent $talentEvent): RedirectResponse
    {
        abort_unless($this->scope->canCreateTalentEvents($request->user()), 403);
        $this->scope->assertTalentEventInScope($request->user(), $talentEvent);

        $copy = $talentEvent->replicate([
            'slug',
            'results_published_at',
            'results_published_by',
            'published_to_students',
            'published_at',
            'is_paused',
        ]);

        $copy->title = $talentEvent->title.' (Copy)';
        $copy->slug = SlugGenerator::unique($copy->title, TalentEvent::class);
        $copy->competition_code = $talentEvent->competition_code
            ? $talentEvent->competition_code.'-COPY'
            : null;
        $copy->status = TalentEventStatus::Scheduled;
        $copy->published_to_students = false;
        $copy->published_at = null;
        $copy->results_published_at = null;
        $copy->results_published_by = null;
        $copy->is_paused = false;
        $copy->created_by = $request->user()->id;
        $copy->save();

        $this->audit->record(
            $request->user(),
            "Duplicated talent event: {$talentEvent->title} → {$copy->title}",
            AuditActionType::Election,
            targetType: 'talent_event',
            targetId: $copy->id,
        );

        return redirect()
            ->route('admin.talent-competition.edit', $copy)
            ->with('success', 'Competition duplicated. Review and save the copy.');
    }

    public function publish(Request $request, TalentEvent $talentEvent): RedirectResponse
    {
        abort_unless($this->scope->canCreateTalentEvents($request->user()), 403);
        $this->scope->assertTalentEventInScope($request->user(), $talentEvent);

        $talentEvent->forceFill([
            'published_to_students' => true,
            'published_at' => $talentEvent->published_at ?? now(),
            'status' => $talentEvent->status === TalentEventStatus::Completed
                ? TalentEventStatus::Scheduled
                : $talentEvent->status,
        ])->save();

        $this->audit->record(
            $request->user(),
            "Published talent competition: {$talentEvent->title}",
            AuditActionType::Election,
            targetType: 'talent_event',
            targetId: $talentEvent->id,
        );

        return back()->with('success', 'Competition published to students.');
    }

    public function archive(Request $request, TalentEvent $talentEvent): RedirectResponse
    {
        abort_unless($this->scope->canCreateTalentEvents($request->user()), 403);
        $this->scope->assertTalentEventInScope($request->user(), $talentEvent);

        $talentEvent->forceFill([
            'status' => TalentEventStatus::Completed,
            'is_paused' => false,
        ])->save();

        $this->audit->record(
            $request->user(),
            "Archived talent competition: {$talentEvent->title}",
            AuditActionType::Election,
            targetType: 'talent_event',
            targetId: $talentEvent->id,
        );

        return back()->with('success', 'Competition archived.');
    }

    public function openRegistration(Request $request, TalentEvent $talentEvent): RedirectResponse
    {
        abort_unless($this->scope->canCreateTalentEvents($request->user()), 403);
        $this->scope->assertTalentEventInScope($request->user(), $talentEvent);

        // Opening registration must take effect immediately. Preserve a future
        // registration_ends_at when still valid; otherwise default to +7 days.
        $talentEvent->forceFill([
            'registration_starts_at' => now(),
            'registration_ends_at' => $talentEvent->registration_ends_at && $talentEvent->registration_ends_at->gt(now())
                ? $talentEvent->registration_ends_at
                : now()->addDays(7),
            'status' => TalentEventStatus::EntriesOpen,
            'published_to_students' => true,
            'published_at' => $talentEvent->published_at ?? now(),
        ])->save();

        $this->announcements->generateForTalentRegistrationOpen($talentEvent->fresh(), $request->user());

        return back()->with('success', 'Registration opened.');
    }

    public function closeRegistration(Request $request, TalentEvent $talentEvent): RedirectResponse
    {
        abort_unless($this->scope->canCreateTalentEvents($request->user()), 403);
        $this->scope->assertTalentEventInScope($request->user(), $talentEvent);

        $talentEvent->forceFill([
            'registration_ends_at' => now(),
            'submission_deadline' => now(),
        ])->save();

        return back()->with('success', 'Registration closed.');
    }

    public function closeVoting(Request $request, TalentEvent $talentEvent): RedirectResponse
    {
        abort_unless($this->scope->canCreateTalentEvents($request->user()), 403);
        $this->scope->assertTalentEventInScope($request->user(), $talentEvent);

        $talentEvent->forceFill([
            'voting_ends_at' => now(),
            'is_paused' => false,
        ])->save();

        $this->notifications->talentVotingClosed($talentEvent->fresh(), $request->user());

        return back()->with('success', 'Voting closed.');
    }

    public function destroy(DeleteTalentCompetitionRequest $request, TalentEvent $talentEvent): RedirectResponse
    {
        $title = $talentEvent->title;
        $eventId = $talentEvent->id;

        // Soft delete — media retained so the competition can be restored later if needed.
        $talentEvent->delete();

        $this->audit->record(
            $request->user(),
            "Deleted talent event: {$title}",
            AuditActionType::Election,
            targetType: 'talent_event',
            targetId: $eventId,
        );

        return redirect()
            ->route('admin.talent-competition.index')
            ->with('success', 'Activity deleted successfully.');
    }

    protected function syncParticipants(TalentEvent $event, array $participants, int $reviewerId): void
    {
        DB::transaction(function () use ($event, $participants, $reviewerId) {
            $keptIds = [];

            foreach ($participants as $participant) {
                $data = [
                    'display_name' => $participant['display_name'],
                    'student_id_number' => $participant['student_id_number'] ?? null,
                    'grade_level' => $participant['grade_level'],
                    'section' => $participant['section'],
                    'course_strand' => $participant['course_strand'] ?? null,
                    'talent_category' => $participant['talent_category'] ?? null,
                    'performance_title' => $participant['performance_title'] ?? null,
                    'profile_summary' => $participant['profile_summary'] ?? null,
                    'performance_description' => $participant['performance_description'] ?? null,
                    'video_url' => $participant['video_url'] ?? null,
                    'social_media' => $participant['social_media'] ?? null,
                    'status' => TalentEventEntry::STATUS_APPROVED,
                    'source' => TalentEventEntry::SOURCE_ADMIN,
                    'reviewed_by' => $reviewerId,
                    'reviewed_at' => now(),
                ];

                if (! empty($participant['id'])) {
                    $entry = TalentEventEntry::query()
                        ->where('talent_event_id', $event->id)
                        ->whereKey($participant['id'])
                        ->first();

                    if ($entry) {
                        $entry->forceFill($data)->save();
                        $keptIds[] = $entry->id;
                    }

                    continue;
                }

                $entry = TalentEventEntry::query()->create([
                    'talent_event_id' => $event->id,
                    'submitted_at' => now(),
                    ...$data,
                ]);

                $keptIds[] = $entry->id;
            }

            TalentEventEntry::query()
                ->where('talent_event_id', $event->id)
                ->where('source', TalentEventEntry::SOURCE_ADMIN)
                ->whereNotIn('id', $keptIds)
                ->delete();
        });
    }

    /** @var array<string, mixed>|null */
    protected ?array $lastStoredImageVariants = null;

    protected function storeTalentEventImage(?UploadedFile $file, string $folder = 'talent-events', bool $withVariants = true): ?string
    {
        $this->lastStoredImageVariants = null;

        if ($file === null) {
            return null;
        }

        if ($withVariants) {
            $set = $this->images->storeOptimizedSet($file, $folder, true);
            $this->lastStoredImageVariants = [
                'medium_path' => $set['medium_path'],
                'mobile_path' => $set['mobile_path'],
                'thumb_path' => $set['thumb_path'],
                'orientation' => $set['orientation'],
                'width' => $set['width'],
                'height' => $set['height'],
            ];

            return $set['path'];
        }

        return $this->images->storeOptimized($file, $folder);
    }

    protected function deleteTalentEventImage(TalentEvent $event): void
    {
        if ($event->image_path) {
            Storage::disk('public')->delete($event->image_path);
        }

        foreach (($event->image_variants ?? []) as $key => $path) {
            if (in_array($key, ['medium_path', 'mobile_path', 'thumb_path'], true) && filled($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    protected function deleteTalentEventThumbnail(TalentEvent $event): void
    {
        if ($event->thumbnail_path) {
            Storage::disk('public')->delete($event->thumbnail_path);
        }
    }

    protected function deleteTalentEventPoster(TalentEvent $event): void
    {
        if ($event->poster_path) {
            Storage::disk('public')->delete($event->poster_path);
        }
    }
}
