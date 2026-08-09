<?php

namespace App\Http\Controllers\Auth;

use App\Enums\NotificationModule;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Election;
use App\Models\Event;
use App\Models\Fundraiser;
use App\Models\PortalNotification;
use App\Services\Portal\PortalNotificationService;
use App\Services\Student\StudentUpcomingActivitiesService;
use App\Services\Talent\StudentTalentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected PortalNotificationService $notificationService,
        protected StudentTalentService $talentService,
        protected StudentUpcomingActivitiesService $upcomingActivities,
    ) {}

    public function student(Request $request): View
    {
        $user = $request->user()->loadCount(['passkeys']);

        $ongoingVotingCount = Election::query()->acceptingVotes()->count();
        $activeEventsCount = Event::query()->upcoming()->count();
        $fundraisingCampaignCount = Fundraiser::query()->acceptingDonations()->count();
        $talentPhase = $this->talentService->activePhaseSummary();
        $openTalentCount = $talentPhase['total'];

        $firstOpenElection = Election::query()
            ->acceptingVotes()
            ->orderBy('voting_ends_at')
            ->first();

        $hasActiveElection = $firstOpenElection !== null;
        $voteNowUrl = $hasActiveElection
            ? route('student.voting.show', $firstOpenElection)
            : route('student.voting.index');

        $talentHref = $talentPhase['registration_open'] > 0 && $talentPhase['voting_open'] === 0
            ? route('student.talent-registration.index')
            : route('student.talent-voting.index');

        $talentStatus = match (true) {
            $talentPhase['registration_open'] > 0 && $talentPhase['voting_open'] > 0 => $openTalentCount.' active competition'.($openTalentCount === 1 ? '' : 's'),
            $talentPhase['registration_open'] > 0 => $talentPhase['registration_open'].' registration'.($talentPhase['registration_open'] === 1 ? '' : 's').' open',
            $talentPhase['voting_open'] > 0 => $talentPhase['voting_open'].' voting'.($talentPhase['voting_open'] === 1 ? '' : 's').' open',
            default => 'No open competitions',
        };

        $talentAction = $talentPhase['registration_open'] > 0 && $talentPhase['voting_open'] === 0
            ? 'Register now →'
            : ($talentPhase['voting_open'] > 0 ? 'Vote now →' : 'View competitions →');

        $unreadAnnouncementCount = PortalNotification::query()
            ->forUser($user)
            ->unread()
            ->where(function ($query) {
                $query->where('module', NotificationModule::Announcement->value)
                    ->orWhere('type', 'student_announcement')
                    ->orWhereNotNull('announcement_id');
            })
            ->count();

        $publishedAnnouncementCount = Announcement::query()
            ->published()
            ->visibleToUser($user)
            ->count();

        $announcementCardCount = $unreadAnnouncementCount > 0
            ? $unreadAnnouncementCount
            : $publishedAnnouncementCount;

        $notificationsCount = $this->notificationService->unreadCountFor($user);

        $activityCards = [
            [
                'key' => 'elections',
                'title' => 'Open Elections',
                'count' => $ongoingVotingCount,
                'status' => $ongoingVotingCount > 0
                    ? $ongoingVotingCount.' '.($ongoingVotingCount === 1 ? 'Active Election' : 'Active Elections')
                    : 'No active elections',
                'action' => 'Vote now →',
                'href' => route('student.voting.index'),
                'enabled' => $ongoingVotingCount > 0,
                'icon' => 'vote',
            ],
            [
                'key' => 'events',
                'title' => 'School Events',
                'count' => $activeEventsCount,
                'status' => $activeEventsCount > 0
                    ? $activeEventsCount.' '.($activeEventsCount === 1 ? 'Upcoming Event' : 'Upcoming Events')
                    : 'No upcoming events',
                'action' => 'View events →',
                'href' => route('student.events.index'),
                'enabled' => $activeEventsCount > 0,
                'icon' => 'events',
            ],
            [
                'key' => 'talent',
                'title' => 'Talent Competitions',
                'count' => $openTalentCount,
                'status' => $talentStatus,
                'action' => $talentAction,
                'href' => $talentHref,
                'enabled' => $openTalentCount > 0,
                'icon' => 'talent',
            ],
            [
                'key' => 'fundraising',
                'title' => 'Fundraising',
                'count' => $fundraisingCampaignCount,
                'status' => $fundraisingCampaignCount > 0
                    ? $fundraisingCampaignCount.' '.($fundraisingCampaignCount === 1 ? 'Active Campaign' : 'Active Campaigns')
                    : 'No active campaigns',
                'action' => 'Support now →',
                'href' => route('student.fundraising.index'),
                'enabled' => $fundraisingCampaignCount > 0,
                'icon' => 'fundraising',
            ],
            [
                'key' => 'announcements',
                'title' => 'Announcements',
                'count' => $announcementCardCount,
                'status' => $unreadAnnouncementCount > 0
                    ? $unreadAnnouncementCount.' '.($unreadAnnouncementCount === 1 ? 'New Announcement' : 'New Announcements')
                    : ($publishedAnnouncementCount > 0
                        ? $publishedAnnouncementCount.' '.($publishedAnnouncementCount === 1 ? 'Announcement' : 'Announcements')
                        : 'No new announcements'),
                'action' => 'Read now →',
                'href' => route('student.announcements.index'),
                'enabled' => $announcementCardCount > 0,
                'icon' => 'announcements',
            ],
        ];

        $notifications = PortalNotification::query()
            ->forUser($user)
            ->limit(5)
            ->get()
            ->map(fn (PortalNotification $notification) => [
                'id' => $notification->id,
                'icon' => $this->notificationService->iconForType(
                    $notification->type,
                    $notification->module instanceof NotificationModule ? $notification->module : null,
                ),
                'title' => $notification->title,
                'message' => $notification->message,
                'time' => $notification->created_at?->diffForHumans() ?? '—',
                'read' => $notification->read_at !== null,
            ]);

        return view('dashboards.student', [
            'user' => $user,
            'firstName' => $user->first_name ?: str($user->name)->before(' ')->toString(),
            'notificationsCount' => $notificationsCount,
            'hasActiveElection' => $hasActiveElection,
            'voteNowUrl' => $voteNowUrl,
            'activityCards' => $activityCards,
            'upcomingSchedule' => $this->upcomingActivities->forDashboard($user),
            'announcements' => Announcement::query()
                ->published()
                ->forDashboard()
                ->visibleToUser($user)
                ->orderByDesc('pin_to_homepage')
                ->orderByDesc('published_at')
                ->limit(5)
                ->get(),
            'notifications' => $notifications,
        ]);
    }
}
