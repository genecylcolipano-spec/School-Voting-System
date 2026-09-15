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
        $hasJudgingAssignment = $showTalent && $this->judging->hasActiveAssignment($user);

        $assignedCompetitionsCount = 0;
        $assignedCompetitions = collect();
        $assignments = collect();
        $progress = [];
        $pastAssignedCount = 0;

        if ($hasJudgingAssignment) {
            $assignedCompetitionsCount = $this->judging->assignedCompetitionsQuery($user, 'current')->count();
            $assignedCompetitions = $this->judging->assignedCompetitionsQuery($user, 'current')
                ->withCount([
                    'entries as approved_entries_count' => fn ($q) => $q->where('status', TalentEventEntry::STATUS_APPROVED),
                ])
                ->limit(6)
                ->get();

            $assignments = TalentEventJudge::query()
                ->active()
                ->where('user_id', $user->id)
                ->whereIn('talent_event_id', $assignedCompetitions->pluck('id'))
                ->get()
                ->keyBy('talent_event_id');

            foreach ($assignedCompetitions as $competition) {
                $progress[$competition->id] = $this->judging->progressFor($user, $competition);
            }

            $pastAssignedCount = $this->judging->assignedCompetitionsQuery($user, 'past')->count();
        }

        return view('faculty.dashboard', [
            'user' => $user,
            'notificationsCount' => AdminPortal::notificationCount($user),
            'openElectionsCount' => Election::query()->visibleToCampus()->acceptingVotes()->count(),
            'upcomingEventsCount' => Event::query()->upcoming()->count(),
            'publishedTalentCount' => $showTalent
                ? TalentEvent::query()->publishedToStudents()->count()
                : 0,
            'activeFundraisersCount' => $showFundraising
                ? Fundraiser::query()->visibleToStudents()->acceptingDonations()->count()
                : 0,
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
}
