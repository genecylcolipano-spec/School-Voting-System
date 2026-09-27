<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Election;
use App\Models\Event;
use App\Models\Fundraiser;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\TalentEventJudge;
use App\Services\Faculty\FacultyUpcomingActivitiesService;
use App\Services\Talent\TalentJudgingService;
use App\Support\AdminPortal;
use App\Support\PlatformModules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Faculty portal dashboard shell.
 */
class FacultyDashboardController extends Controller
{
    public function __construct(
        protected TalentJudgingService $judging,
        protected FacultyUpcomingActivitiesService $upcomingActivities,
    ) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user()->loadCount('passkeys');
        $showTalent = PlatformModules::talent();
        $showFundraising = PlatformModules::fundraising();
        $counts = $this->sharedCounts($showTalent, $showFundraising);

        $assignedCompetitionsCount = 0;
        $assignedCompetitions = collect();
        $assignments = collect();
        $progress = [];
        $pastAssignedCount = 0;
        $hasJudgingAssignment = false;

        if ($showTalent) {
            $currentAssignments = $this->judging->assignedCompetitionsQuery($user, 'current')
                ->withCount([
                    'entries as approved_entries_count' => fn ($q) => $q->where('status', TalentEventEntry::STATUS_APPROVED),
                ])
                ->get();

            $assignedCompetitionsCount = $currentAssignments->count();
            $assignedCompetitions = $currentAssignments->take(6)->values();
            $pastAssignedCount = $this->judging->assignedCompetitionsQuery($user, 'past')->count();
            $hasJudgingAssignment = $assignedCompetitionsCount > 0 || $pastAssignedCount > 0;

            if ($hasJudgingAssignment && $assignedCompetitions->isNotEmpty()) {
                $assignments = TalentEventJudge::query()
                    ->active()
                    ->where('user_id', $user->id)
                    ->whereIn('talent_event_id', $assignedCompetitions->pluck('id'))
                    ->get()
                    ->keyBy('talent_event_id');

                $progress = $this->judging->progressForMany($user, $assignedCompetitions)->all();
            }
        }

        return view('faculty.dashboard', [
            'user' => $user,
            'notificationsCount' => AdminPortal::notificationCount($user),
            'openElectionsCount' => $counts['open_elections'],
            'upcomingEventsCount' => $counts['upcoming_events'],
            'publishedTalentCount' => $counts['published_talent'],
            'activeFundraisersCount' => $counts['active_fundraisers'],
            'showTalent' => $showTalent,
            'showFundraising' => $showFundraising,
            'hasJudgingAssignment' => $hasJudgingAssignment,
            'assignedCompetitionsCount' => $assignedCompetitionsCount,
            'pastAssignedCount' => $pastAssignedCount,
            'assignedCompetitions' => $assignedCompetitions,
            'assignments' => $assignments,
            'progress' => $progress,
            'upcomingSchedule' => $this->upcomingActivities->forDashboard($user),
            'announcements' => Announcement::query()
                ->published()
                ->forDashboard()
                ->visibleToUser($user)
                ->orderByDesc('pin_to_homepage')
                ->limit(5)
                ->get(),
        ]);
    }

    /**
     * @return array{open_elections: int, upcoming_events: int, published_talent: int, active_fundraisers: int}
     */
    protected function sharedCounts(bool $showTalent, bool $showFundraising): array
    {
        $cached = Cache::remember('faculty.dashboard.shared_counts', 45, function () {
            return [
                'open_elections' => Election::query()->visibleToCampus()->acceptingVotes()->count(),
                'upcoming_events' => Event::query()->upcoming()->count(),
                'published_talent' => TalentEvent::query()->publishedToStudents()->count(),
                'active_fundraisers' => Fundraiser::query()->visibleToStudents()->acceptingDonations()->count(),
            ];
        });

        return [
            'open_elections' => $cached['open_elections'],
            'upcoming_events' => $cached['upcoming_events'],
            'published_talent' => $showTalent ? $cached['published_talent'] : 0,
            'active_fundraisers' => $showFundraising ? $cached['active_fundraisers'] : 0,
        ];
    }
}
