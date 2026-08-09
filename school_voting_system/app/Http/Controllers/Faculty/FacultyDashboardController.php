<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Election;
use App\Models\Event;
use App\Models\TalentEventEntry;
use App\Models\TalentEventJudge;
use App\Services\Talent\TalentJudgingService;
use App\Support\AdminPortal;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Faculty portal dashboard shell.
 */
class FacultyDashboardController extends Controller
{
    public function __construct(
        protected TalentJudgingService $judging,
    ) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user()->loadCount('passkeys');

        $assignedCompetitions = $this->judging->assignedCompetitionsQuery($user)
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

        $progress = [];
        foreach ($assignedCompetitions as $competition) {
            $progress[$competition->id] = $this->judging->progressFor($user, $competition);
        }

        return view('faculty.dashboard', [
            'user' => $user,
            'notificationsCount' => AdminPortal::notificationCount($user),
            'openElectionsCount' => Election::query()->acceptingVotes()->count(),
            'upcomingEventsCount' => Event::query()->upcoming()->count(),
            'openTalentCount' => $assignedCompetitions->count(),
            'assignedCompetitions' => $assignedCompetitions,
            'assignments' => $assignments,
            'progress' => $progress,
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
