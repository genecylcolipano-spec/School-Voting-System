<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\Portal\PortalNotificationService;
use App\Services\Student\StudentOverviewService;
use App\Services\Student\StudentUpcomingActivitiesService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected PortalNotificationService $notificationService,
        protected StudentUpcomingActivitiesService $upcomingActivities,
        protected StudentOverviewService $overview,
    ) {}

    public function student(Request $request): View
    {
        $user = $request->user()->loadCount(['passkeys']);
        $overview = $this->overview->forStudent($user);

        return view('dashboards.student', [
            'user' => $user,
            'firstName' => $user->first_name ?: str($user->name)->before(' ')->toString(),
            'notificationsCount' => $this->notificationService->unreadCountFor($user),
            'canVoteNow' => $overview['can_vote_now'],
            'voteNowUrl' => $overview['vote_now_url'],
            'voteNowHint' => $overview['vote_now_hint'],
            'activityCards' => $overview['cards'],
            'upcomingSchedule' => $this->upcomingActivities->forDashboard($user),
            'announcements' => Announcement::query()
                ->published()
                ->forDashboard()
                ->visibleToUser($user)
                ->orderByDesc('pin_to_homepage')
                ->orderByDesc('published_at')
                ->limit(5)
                ->get(),
        ]);
    }
}
