<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditActionType;
use App\Http\Controllers\Admin\Concerns\LogsAdminActions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Announcement\DeleteAnnouncementRequest;
use App\Http\Requests\Admin\Announcement\StoreAnnouncementRequest;
use App\Http\Requests\Admin\Announcement\UpdateAnnouncementRequest;
use App\Models\Announcement;
use App\Models\AnnouncementAttachment;
use App\Services\Media\ImageCompressionService;
use App\Services\Portal\AnnouncementService;
use App\Services\Portal\PortalNotificationService;
use App\Support\SlugGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAnnouncementController extends Controller
{
    use LogsAdminActions;

    public function __construct(
        protected PortalNotificationService $notifications,
        protected AnnouncementService $announcements,
        protected ImageCompressionService $images,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Announcement::class);

        $announcements = Announcement::query()
            ->with(['author', 'updater'])
            ->latest()
            ->paginate(15);

        return view('admin.announcements.index', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => $this->recoveryCount(),
            'announcements' => $announcements,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Announcement::class);

        return view('admin.announcements.create', $this->formData($request));
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $banner = $this->storeBanner($request->file('banner'));

        $announcement = Announcement::query()->create([
            ...$this->announcements->payloadFromValidated($validated),
            'slug' => SlugGenerator::unique($validated['title'], Announcement::class),
            'banner_path' => $banner['path'] ?? null,
            'banner_variants' => $banner['variants'] ?? null,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        $this->announcements->storeAttachments(
            $announcement,
            $request->file('attachments', []),
            $request->user(),
        );

        if ($announcement->is_published) {
            $this->announcements->dispatchNotificationsIfNeeded($announcement, $request->user(), true);
        }

        $this->logAdminAction('Created announcement: '.$announcement->title, AuditActionType::Election, 'announcement', $announcement->id);

        return redirect()->route('admin.announcements.edit', $announcement)->with('success', 'Announcement created.');
    }

    public function edit(Request $request, Announcement $announcement): View
    {
        $this->authorize('view', $announcement);

        $announcement->load(['author', 'updater', 'attachments']);
        $announcement->loadCount('views');
        $announcement->loadSum('attachments', 'download_count');

        return view('admin.announcements.edit', [
            ...$this->formData($request),
            'announcement' => $announcement,
            'stats' => $announcement->statistics(),
        ]);
    }

    public function preview(Request $request, Announcement $announcement): View
    {
        $this->authorize('view', $announcement);

        $announcement->load('attachments');

        return view('student.announcements.show', [
            'user' => $request->user()->loadCount('passkeys'),
            'announcement' => $announcement,
            'preview' => true,
        ]);
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $validated = $request->validated();
        $payload = $this->announcements->payloadFromValidated($validated);
        $payload['slug'] = SlugGenerator::unique($validated['title'], Announcement::class, $announcement->id);
        $payload['updated_by'] = $request->user()->id;

        if ($request->hasFile('banner')) {
            $announcement->deleteBannerFiles();
            $banner = $this->storeBanner($request->file('banner'));
            $payload['banner_path'] = $banner['path'] ?? null;
            $payload['banner_variants'] = $banner['variants'] ?? null;
        }

        $wasPublished = $announcement->is_published;
        $announcement->update($payload);

        foreach ($validated['remove_attachment_ids'] ?? [] as $attachmentId) {
            $attachment = AnnouncementAttachment::query()
                ->where('announcement_id', $announcement->id)
                ->find($attachmentId);

            if ($attachment) {
                $this->announcements->deleteAttachment($attachment);
            }
        }

        $this->announcements->storeAttachments(
            $announcement,
            $request->file('attachments', []),
            $request->user(),
        );

        $shouldNotify = $announcement->is_published && (
            ! $wasPublished
            || ($validated['resend_notifications'] ?? false)
        );

        if ($shouldNotify) {
            $this->announcements->dispatchNotificationsIfNeeded(
                $announcement->fresh(),
                $request->user(),
                (bool) ($validated['resend_notifications'] ?? false),
            );
        }

        $this->logAdminAction('Updated announcement: '.$announcement->title, AuditActionType::Election, 'announcement', $announcement->id);

        return redirect()->route('admin.announcements.edit', $announcement)->with('success', 'Announcement updated.');
    }

    public function destroy(DeleteAnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $title = $announcement->title;
        $announcementId = $announcement->id;

        $announcement->deleteBannerFiles();
        foreach ($announcement->attachments as $attachment) {
            $this->announcements->deleteAttachment($attachment);
        }
        $announcement->delete();

        $this->logAdminAction('Deleted announcement: '.$title, AuditActionType::Election, 'announcement', $announcementId);

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement deleted.');
    }

    public function downloadAttachment(Request $request, Announcement $announcement, AnnouncementAttachment $attachment): StreamedResponse
    {
        $this->authorize('view', $announcement);

        abort_unless($attachment->announcement_id === $announcement->id, 404);
        abort_unless(Storage::disk('public')->exists($attachment->path), 404);

        $attachment->increment('download_count');

        return Storage::disk('public')->download($attachment->path, $attachment->original_name);
    }

    /**
     * @return array<string, mixed>
     */
    protected function formData(Request $request): array
    {
        return [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => $this->recoveryCount(),
            ...$this->announcements->formOptions(),
        ];
    }

    /**
     * @return array{path: ?string, variants: ?array<string, mixed>}
     */
    protected function storeBanner(?UploadedFile $file): array
    {
        if (! $file) {
            return ['path' => null, 'variants' => null];
        }

        $set = $this->images->storeOptimizedSet($file, 'announcements/banners', true);

        return [
            'path' => $set['path'],
            'variants' => [
                'medium_path' => $set['medium_path'] ?? null,
                'mobile_path' => $set['mobile_path'] ?? null,
                'thumb_path' => $set['thumb_path'] ?? null,
                'orientation' => $set['orientation'] ?? null,
                'width' => $set['width'] ?? null,
                'height' => $set['height'] ?? null,
            ],
        ];
    }
}
