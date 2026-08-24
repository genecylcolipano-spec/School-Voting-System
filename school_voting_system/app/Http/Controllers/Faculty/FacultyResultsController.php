<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\TalentEvent;
use App\Services\Faculty\FacultyResultsService;
use App\Support\AdminPortal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FacultyResultsController extends Controller
{
    public function __construct(
        protected FacultyResultsService $results,
    ) {}

    public function index(Request $request): View
    {
        $events = $this->results->listPublished();

        return view('faculty.results.index', [
            ...$this->portalData($request),
            'events' => $events,
            'hasEvents' => $events->isNotEmpty(),
        ]);
    }

    public function showElection(Request $request, Election $election): View
    {
        $this->results->assertPublishedElection($election);

        return view('faculty.results.show', [
            ...$this->portalData($request),
            'detail' => $this->results->electionDetail($election),
            'backUrl' => route('faculty.results.index'),
            'backLabel' => 'All Results',
        ]);
    }

    public function showTalent(Request $request, TalentEvent $talentEvent): View
    {
        $this->results->assertPublishedTalent($talentEvent);

        $fromAssigned = $request->string('from')->toString() === 'assigned';

        return view('faculty.results.show', [
            ...$this->portalData($request),
            'detail' => $this->results->talentDetail($talentEvent),
            'backUrl' => $fromAssigned
                ? route('faculty.judging.index', ['filter' => 'past'])
                : route('faculty.results.index'),
            'backLabel' => $fromAssigned ? 'Assigned competitions' : 'All Results',
        ]);
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
