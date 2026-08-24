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
        $showOpenOnly = $request->string('filter')->toString() === 'open';

        $elections = Election::query()
            ->visibleToCampus()
            ->when($showOpenOnly, fn ($query) => $query->acceptingVotes())
            ->orderByDesc('voting_starts_at')
            ->paginate(10)
            ->withQueryString();

        return view('faculty.elections.index', [
            ...$this->portalData($request),
            'elections' => $elections,
            'showOpenOnly' => $showOpenOnly,
            'openCount' => Election::query()->visibleToCampus()->acceptingVotes()->count(),
            'allCount' => Election::query()->visibleToCampus()->count(),
        ]);
    }

    public function electionShow(Request $request, Election $election): View
    {
        abort_unless($election->isVisibleToCampus(), 404);

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
        Event::markOverdueAsCompleted();

        $showUpcomingOnly = $request->string('filter')->toString() === 'upcoming';

        $events = Event::query()
            ->when(
                $showUpcomingOnly,
                fn ($query) => $query->upcoming()->orderBy('event_date'),
                fn ($query) => $query->campusListing(),
            )
            ->paginate(12)
            ->withQueryString();

        return view('faculty.events.index', [
            ...$this->portalData($request),
            'events' => $events,
            'showUpcomingOnly' => $showUpcomingOnly,
            'upcomingCount' => Event::query()->upcoming()->count(),
            'allCount' => Event::query()->visibleToCampus()->count(),
        ]);
    }

    public function eventShow(Request $request, Event $event): View
    {
        abort_unless($event->isVisibleToCampus(), 404);
        Event::markOverdueAsCompleted();
        $event->refresh();

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
        abort_unless($this->announcements->userCanView($announcement, $request->user()), 403);

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
        abort_unless($this->announcements->userCanView($announcement, $request->user()), 403);
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
