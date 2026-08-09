<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Concerns\ManagesPortalNotifications;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementAttachment;
use App\Models\Election;
use App\Models\Event;
use App\Services\Portal\AnnouncementService;
use App\Support\AdminPortal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FacultyPortalController extends Controller
{
    use ManagesPortalNotifications;

    public function __construct(
        protected AnnouncementService $announcements,
    ) {}

    public function elections(Request $request): View
    {
        $elections = Election::query()
            ->orderByDesc('voting_starts_at')
            ->paginate(10);

        return view('faculty.elections.index', [
            ...$this->portalData($request),
            'elections' => $elections,
        ]);
    }

    public function electionShow(Request $request, Election $election): View
    {
        $election->loadMissing([
            'categories',
            'activeCandidates',
            'activeCandidates.user',
        ]);

        return view('faculty.elections.show', [
            ...$this->portalData($request),
            'election' => $election,
        ]);
    }

    public function events(Request $request): View
    {
        $events = Event::query()
            ->orderBy('event_date')
            ->paginate(12);

        return view('faculty.events.index', [
            ...$this->portalData($request),
            'events' => $events,
        ]);
    }

    public function eventShow(Request $request, Event $event): View
    {
        return view('faculty.events.show', [
            ...$this->portalData($request),
            'event' => $event,
        ]);
    }

    public function announcements(Request $request): View
    {
        $user = $request->user();

        $announcements = Announcement::query()
            ->published()
            ->visibleToUser($user)
            ->with('attachments')
            ->paginate(12);

        return view('faculty.announcements.index', [
            ...$this->portalData($request),
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

        return view('faculty.announcements.show', [
            ...$this->portalData($request),
            'announcement' => $announcement,
        ]);
    }

    public function downloadAnnouncementAttachment(
        Request $request,
        Announcement $announcement,
        AnnouncementAttachment $attachment,
    ): StreamedResponse {
        abort_unless($announcement->isLive(), 404);
        abort_unless($attachment->announcement_id === $announcement->id, 404);
        abort_unless(
            $this->announcements->recipientQuery($announcement)->where('id', $request->user()->id)->exists(),
            403,
        );
        abort_unless(Storage::disk('public')->exists($attachment->path), 404);

        $attachment->increment('download_count');

        return Storage::disk('public')->download($attachment->path, $attachment->original_name);
    }

    public function notifications(Request $request): View
    {
        return $this->notificationIndexView(
            $request,
            'faculty.notifications.index',
            route('faculty.notifications.index'),
        );
    }

    public function notificationsFeed(Request $request): JsonResponse
    {
        return $this->feedJson($request);
    }

    /**
     * @return array{user: \App\Models\User, notificationsCount: int}
     */
    protected function portalData(Request $request): array
    {
        $user = $request->user()->loadCount('passkeys');

        return [
            'user' => $user,
            'notificationsCount' => AdminPortal::notificationCount($user),
        ];
    }
}
