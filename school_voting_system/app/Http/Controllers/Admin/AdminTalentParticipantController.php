<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditActionType;
use App\Enums\TalentCategory;
use App\Enums\TalentRegistrationMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TalentCompetition\StoreTalentParticipantRequest;
use App\Http\Requests\Admin\TalentCompetition\UpdateTalentParticipantRequest;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Services\Admin\AdminScopeService;
use App\Services\Media\ImageCompressionService;
use App\Services\Portal\PortalNotificationService;
use App\Services\SuperAdmin\AuditLogService;
use App\Services\Talent\TalentEventPublishingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminTalentParticipantController extends Controller
{
    public function __construct(
        protected AdminScopeService $scope,
        protected AuditLogService $audit,
        protected ImageCompressionService $images,
        protected TalentEventPublishingService $publishing,
        protected PortalNotificationService $notifications,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user()->load(['staffRole', 'passkeys']);
        $eventIds = $this->scopedEventIds($user);

        $status = $request->query('status');
        $validStatuses = ['pending', 'approved', 'rejected', 'withdrawn', 'disqualified', 'archived'];
        $selectedEvent = $request->query('event');
        $search = trim((string) $request->query('q', ''));

        $baseQuery = TalentEventEntry::query()
            ->whereIn('talent_event_id', $eventIds->isEmpty() ? [-1] : $eventIds)
            ->when($selectedEvent, fn ($q) => $q->where('talent_event_id', $selectedEvent))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('display_name', 'like', "%{$search}%")
                        ->orWhere('student_id_number', 'like', "%{$search}%")
                        ->orWhere('performance_title', 'like', "%{$search}%")
                        ->orWhere('course_strand', 'like', "%{$search}%");
                });
            });

        $counts = [
            'all' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', TalentEventEntry::STATUS_PENDING)->count(),
            'approved' => (clone $baseQuery)->where('status', TalentEventEntry::STATUS_APPROVED)->count(),
            'rejected' => (clone $baseQuery)->where('status', TalentEventEntry::STATUS_REJECTED)->count(),
            'withdrawn' => (clone $baseQuery)->where('status', TalentEventEntry::STATUS_WITHDRAWN)->count(),
            'disqualified' => (clone $baseQuery)->where('status', TalentEventEntry::STATUS_DISQUALIFIED)->count(),
            'archived' => (clone $baseQuery)->where('status', TalentEventEntry::STATUS_ARCHIVED)->count(),
        ];

        $participants = (clone $baseQuery)
            ->when(in_array($status, $validStatuses, true), fn ($q) => $q->where('status', $status))
            ->with(['talentEvent:id,title,slug,talent_category', 'student:id,name,email'])
            ->orderByDesc('submitted_at')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $events = TalentEvent::query()
            ->whereIn('id', $eventIds->isEmpty() ? [-1] : $eventIds)
            ->orderByDesc('event_date')
            ->get(['id', 'title', 'registration_method']);

        return view('admin.talent-participants.index', [
            'user' => $user,
            'notificationsCount' => 0,
            'participants' => $participants,
            'counts' => $counts,
            'activeStatus' => in_array($status, $validStatuses, true) ? $status : 'all',
            'events' => $events,
            'selectedEvent' => $selectedEvent,
            'search' => $search,
            'canManage' => $this->scope->canCreateTalentEvents($user),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($this->scope->canCreateTalentEvents($request->user()), 403);

        $user = $request->user()->load(['staffRole', 'passkeys']);
        $eventIds = $this->scopedEventIds($user);

        $events = TalentEvent::query()
            ->whereIn('id', $eventIds->isEmpty() ? [-1] : $eventIds)
            ->get()
            ->filter(fn (TalentEvent $event) => ($event->registration_method ?? TalentRegistrationMethod::Both)->allowsAdminManaged())
            ->values();

        $preselected = $request->query('event');

        return view('admin.talent-participants.create', [
            'user' => $user,
            'notificationsCount' => 0,
            'events' => $events,
            'categories' => TalentCategory::cases(),
            'preselectedEvent' => $preselected,
            'entry' => null,
        ]);
    }

    public function store(StoreTalentParticipantRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $event = TalentEvent::query()->findOrFail($validated['talent_event_id']);

        $approve = $request->boolean('approve_immediately', true);

        $entry = TalentEventEntry::query()->create([
            'talent_event_id' => $event->id,
            'display_name' => $validated['display_name'],
            'student_id_number' => $validated['student_id_number'] ?? null,
            'grade_level' => $validated['grade_level'],
            'section' => $validated['section'],
            'course_strand' => $validated['course_strand'] ?? null,
            'talent_category' => $validated['talent_category'] ?? $event->talent_category?->value,
            'performance_title' => $validated['performance_title'] ?? null,
            'profile_summary' => $validated['profile_summary'] ?? null,
            'performance_description' => $validated['performance_description'] ?? null,
            'social_media' => $validated['social_media'] ?? null,
            'photo_path' => $this->storePublicImage($request->file('photo'), 'talent/photos'),
            'thumbnail_path' => $this->storePublicImage($request->file('thumbnail'), 'talent/thumbnails'),
            'video_path' => $this->storePrivateVideo($request->file('video')),
            'video_url' => $validated['video_url'] ?? null,
            'status' => $approve ? TalentEventEntry::STATUS_APPROVED : TalentEventEntry::STATUS_PENDING,
            'source' => TalentEventEntry::SOURCE_ADMIN,
            'submitted_at' => now(),
            'reviewed_by' => $approve ? $request->user()->id : null,
            'reviewed_at' => $approve ? now() : null,
        ]);

        if ($approve) {
            $this->publishing->publishIfReady($event->fresh(), $request->user());
        }

        $this->audit->record(
            $request->user(),
            "Added talent participant: {$entry->display_name} ({$event->title})",
            AuditActionType::Election,
            targetType: 'talent_event_entry',
            targetId: $entry->id,
        );

        return redirect()
            ->route('admin.talent-participants.show', $entry)
            ->with('success', 'Participant added successfully.');
    }

    public function show(Request $request, TalentEventEntry $entry): View
    {
        $this->scope->assertTalentEntryInScope($request->user(), $entry);

        $entry->load(['talentEvent', 'student', 'reviewer']);

        return view('admin.talent-participants.show', [
            'user' => $request->user()->load(['staffRole', 'passkeys']),
            'notificationsCount' => 0,
            'entry' => $entry,
            'canManage' => $this->scope->canCreateTalentEvents($request->user()),
        ]);
    }

    public function edit(Request $request, TalentEventEntry $entry): View
    {
        abort_unless($this->scope->canCreateTalentEvents($request->user()), 403);
        $this->scope->assertTalentEntryInScope($request->user(), $entry);

        $entry->load('talentEvent');

        return view('admin.talent-participants.edit', [
            'user' => $request->user()->load(['staffRole', 'passkeys']),
            'notificationsCount' => 0,
            'entry' => $entry,
            'categories' => TalentCategory::cases(),
        ]);
    }

    public function update(UpdateTalentParticipantRequest $request, TalentEventEntry $entry): RedirectResponse
    {
        $validated = $request->validated();

        $photoPath = $entry->photo_path;
        $thumbnailPath = $entry->thumbnail_path;
        $videoPath = $entry->video_path;
        $videoUrl = $validated['video_url'] ?? $entry->video_url;

        if ($request->hasFile('photo')) {
            $this->deletePublicPath($photoPath);
            $photoPath = $this->storePublicImage($request->file('photo'), 'talent/photos');
        }

        if ($request->hasFile('thumbnail')) {
            $this->deletePublicPath($thumbnailPath);
            $thumbnailPath = $this->storePublicImage($request->file('thumbnail'), 'talent/thumbnails');
        }

        if ($request->boolean('remove_video') && $videoPath) {
            $this->deletePrivatePath($videoPath);
            $videoPath = null;
        }

        if ($request->hasFile('video')) {
            $this->deletePrivatePath($videoPath);
            $videoPath = $this->storePrivateVideo($request->file('video'));
        }

        if ($request->boolean('clear_video_url')) {
            $videoUrl = null;
        }

        $entry->forceFill([
            'display_name' => $validated['display_name'],
            'student_id_number' => $validated['student_id_number'] ?? null,
            'grade_level' => $validated['grade_level'],
            'section' => $validated['section'],
            'course_strand' => $validated['course_strand'] ?? null,
            'talent_category' => $validated['talent_category'] ?? $entry->talent_category?->value,
            'performance_title' => $validated['performance_title'] ?? null,
            'profile_summary' => $validated['profile_summary'] ?? null,
            'performance_description' => $validated['performance_description'] ?? null,
            'social_media' => $validated['social_media'] ?? null,
            'photo_path' => $photoPath,
            'thumbnail_path' => $thumbnailPath,
            'video_path' => $videoPath,
            'video_url' => $videoUrl,
        ])->save();

        $this->audit->record(
            $request->user(),
            "Updated talent participant: {$entry->display_name}",
            AuditActionType::Election,
            targetType: 'talent_event_entry',
            targetId: $entry->id,
        );

        return redirect()
            ->route('admin.talent-participants.show', $entry)
            ->with('success', 'Participant updated successfully.');
    }

    public function destroy(Request $request, TalentEventEntry $entry): RedirectResponse
    {
        abort_unless($this->scope->canCreateTalentEvents($request->user()), 403);
        $this->scope->assertTalentEntryInScope($request->user(), $entry);

        $label = $entry->display_name;
        $eventTitle = $entry->talentEvent?->title;
        $entryId = $entry->id;

        $entry->delete();

        $this->audit->record(
            $request->user(),
            "Deleted talent participant: {$label} ({$eventTitle})",
            AuditActionType::Election,
            targetType: 'talent_event_entry',
            targetId: $entryId,
        );

        return redirect()
            ->route('admin.talent-participants.index')
            ->with('success', 'Participant removed.');
    }

    /**
     * @return \Illuminate\Support\Collection<int, int>
     */
    protected function scopedEventIds($user)
    {
        if ($user->isSuperAdmin()) {
            return TalentEvent::query()->pluck('id');
        }

        return $this->scope->talentEvents($user)->pluck('id');
    }

    protected function storePublicImage(?UploadedFile $file, string $folder): ?string
    {
        if ($file === null) {
            return null;
        }

        return $this->images->storeOptimized($file, $folder);
    }

    protected function storePrivateVideo(?UploadedFile $file): ?string
    {
        if ($file === null) {
            return null;
        }

        return $file->store('talent/videos', 'local');
    }

    protected function deletePublicPath(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }

    protected function deletePrivatePath(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'http')) {
            Storage::disk('local')->delete($path);
        }
    }
}
