<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EventStatus;
use App\Enums\TalentEventStatus;
use App\Http\Controllers\Admin\Concerns\LogsAdminActions;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\Admin\AdminScopeService;
use App\Support\AdminPortal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminEventsTalentController extends Controller
{
    use LogsAdminActions;

    public function __construct(protected AdminScopeService $scope) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $election = $this->scope->assignedElection($user);

        Event::markOverdueAsCompleted();

        $talentQuery = $this->scope->talentEventsQuery($user);
        $schoolQuery = $this->scope->schoolEventsQuery($user);

        $openCompetitionStatuses = [
            TalentEventStatus::Scheduled,
            TalentEventStatus::EntriesOpen,
            TalentEventStatus::VotingOpen,
        ];

        $talentEvents = (clone $talentQuery)
            ->withCount('entries')
            ->latest('event_date')
            ->limit(6)
            ->get();

        $schoolEvents = (clone $schoolQuery)
            ->latest('event_date')
            ->limit(6)
            ->get();

        return view('admin.events-talent.index', [
            'user' => $user->loadCount('passkeys'),
            'notificationsCount' => AdminPortal::notificationCount($user),
            'election' => $election,
            'talentEvents' => $talentEvents,
            'schoolEvents' => $schoolEvents,
            'overview' => [
                'competitions_open' => (clone $talentQuery)->whereIn('status', $openCompetitionStatuses)->count(),
                'competitions_total' => (clone $talentQuery)->count(),
                'school_events_upcoming' => (clone $schoolQuery)
                    ->where('status', EventStatus::Scheduled)
                    ->where('event_date', '>=', now())
                    ->count(),
                'school_events_total' => (clone $schoolQuery)->count(),
            ],
            'canCreateTalent' => $this->scope->canCreateTalentEvents($user),
            'canCreateEvents' => $user->can('create', Event::class),
        ]);
    }
}
