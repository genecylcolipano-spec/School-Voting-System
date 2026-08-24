<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\LogsAdminActions;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\Admin\AdminScopeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminEventsTalentController extends Controller
{
    use LogsAdminActions;

    public function __construct(protected AdminScopeService $scope) {}

    public function index(Request $request): View
    {
        $election = $this->scope->assignedElection($request->user());

        Event::markOverdueAsCompleted();

        $talentEvents = $this->scope->talentEventsQuery($request->user())
            ->withCount('entries')
            ->latest('event_date')
            ->limit(6)
            ->get();

        return view('admin.events-talent.index', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => $this->recoveryCount(),
            'election' => $election,
            'talentEvents' => $talentEvents,
            'schoolEvents' => Event::query()->latest('event_date')->limit(6)->get(),
            'canCreateTalent' => $this->scope->canCreateTalentEvents($request->user()),
            'canCreateEvents' => $request->user()->can('create', Event::class),
        ]);
    }
}
