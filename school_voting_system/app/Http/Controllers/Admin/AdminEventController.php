<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditActionType;
use App\Enums\EventStatus;
use App\Http\Controllers\Admin\Concerns\LogsAdminActions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SchoolEvent\DeleteSchoolEventRequest;
use App\Http\Requests\Admin\SchoolEvent\StoreSchoolEventRequest;
use App\Http\Requests\Admin\SchoolEvent\UpdateSchoolEventRequest;
use App\Models\Event;
use App\Services\Media\ImageCompressionService;
use App\Services\Portal\AnnouncementService;
use App\Services\Portal\PortalNotificationService;
use App\Support\SlugGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminEventController extends Controller
{
    use LogsAdminActions;

    public function __construct(
        protected ImageCompressionService $images,
        protected AnnouncementService $announcements,
        protected PortalNotificationService $notifications,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Event::class);
        $events = Event::query()
            ->orderByDesc('event_date')
            ->paginate(15);

        return view('admin.events.index', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => $this->recoveryCount(),
            'events' => $events,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Event::class);

        return view('admin.events.create', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => $this->recoveryCount(),
            'statuses' => EventStatus::cases(),
        ]);
    }

    public function store(StoreSchoolEventRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $event = Event::query()->create([
            'title' => $validated['title'],
            'slug' => SlugGenerator::unique($validated['title'], Event::class),
            'description' => $validated['description'] ?? null,
            'image_path' => $this->storeEventImage($request->file('image')),
            'image_variants' => $this->lastStoredImageVariants,
            'event_date' => $validated['event_date'],
            'venue' => $validated['venue'],
            'status' => $validated['status'],
            'created_by' => $request->user()->id,
        ]);

        $this->logAdminAction('Created school event: '.$validated['title'], AuditActionType::Election, 'event');
        $this->announcements->generateForSchoolEvent($event, $request->user());

        if ($event->status === EventStatus::Scheduled) {
            $this->notifications->schoolEventPublished($event->title, $request->user(), $event->id);
        }

        return redirect()->route('admin.events.index')->with('success', 'Event created.');
    }

    public function edit(Request $request, Event $event): View
    {
        $this->authorize('view', $event);

        return view('admin.events.edit', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => $this->recoveryCount(),
            'event' => $event,
            'statuses' => EventStatus::cases(),
        ]);
    }

    public function update(UpdateSchoolEventRequest $request, Event $event): RedirectResponse
    {
        $validated = $request->validated();

        $imagePath = $event->image_path;
        $imageVariants = $event->image_variants;

        if ($request->hasFile('image')) {
            $this->deleteEventImage($event);
            $imagePath = $this->storeEventImage($request->file('image'));
            $imageVariants = $this->lastStoredImageVariants;
        }

        $previousStatus = $event->status;

        $event->update([
            'title' => $validated['title'],
            'slug' => SlugGenerator::unique($validated['title'], Event::class, $event->id),
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'image_variants' => $imageVariants,
            'event_date' => $validated['event_date'],
            'venue' => $validated['venue'],
            'status' => $validated['status'],
        ]);

        $this->logAdminAction('Updated school event: '.$event->title, AuditActionType::Election, 'event', $event->id);

        $becameScheduled = $event->status === EventStatus::Scheduled
            && $previousStatus !== EventStatus::Scheduled;

        if ($becameScheduled) {
            $this->notifications->schoolEventPublished($event->title, $request->user(), $event->id);
        }

        return redirect()->route('admin.events.index')->with('success', 'Event updated.');
    }

    public function destroy(DeleteSchoolEventRequest $request, Event $event): RedirectResponse
    {
        $title = $event->title;
        $eventId = $event->id;

        // Soft delete — keep media so the activity can be restored later if needed.
        $event->delete();

        $this->logAdminAction('Deleted school event: '.$title, AuditActionType::Election, 'event', $eventId);

        return redirect()->route('admin.events.index')->with('success', 'Activity deleted successfully.');
    }

    /** @var array<string, mixed>|null */
    protected ?array $lastStoredImageVariants = null;

    protected function storeEventImage(?UploadedFile $file): ?string
    {
        $this->lastStoredImageVariants = null;

        if ($file === null) {
            return null;
        }

        $set = $this->images->storeOptimizedSet($file, 'events', true);
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

    protected function deleteEventImage(Event $event): void
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
}
