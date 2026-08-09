<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Election;
use App\Models\Fundraiser;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Services\Admin\AdminResultsService;
use App\Services\Admin\AdminScopeService;
use App\Support\AdminPortal;
use App\Support\WinnerSpotlightBuilder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminReportController extends Controller
{
    public function __construct(
        protected AdminScopeService $scope,
        protected AdminResultsService $results,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user()->loadCount('passkeys');
        $election = $this->scope->assignedElection($user);
        $statistics = $this->scope->statistics($user);
        $turnoutSections = $this->scope->turnoutBySection($user);

        $report = null;
        $exportUrls = null;
        $winningParty = null;

        if ($election instanceof Election) {
            $detail = $this->results->electionDetail($election, $user);
            $spotlight = WinnerSpotlightBuilder::fromRankings($detail['rankings'] ?? []);
            $winningParty = collect($detail['party_performance'] ?? [])->sortByDesc('total_votes')->first();

            $report = [
                'election_name' => $detail['name'],
                'total_votes' => $detail['summary']['total_votes'] ?? 0,
                'turnout_percent' => $detail['summary']['turnout_percent'] ?? 0,
                'participants' => $detail['summary']['participants'] ?? 0,
                'winners' => $spotlight,
                'party_performance' => $detail['party_performance'] ?? [],
                'turnout_sections' => $detail['turnout_sections'] ?? [],
            ];

            $exportUrls = [
                'pdf' => route('admin.results.election.export', [$election, 'format' => 'pdf']),
                'excel' => route('admin.results.election.export', [$election, 'format' => 'excel']),
                'print' => route('admin.results.election.export', [$election, 'format' => 'print']),
            ];
        }

        return view('admin.reports.index', [
            'user' => $user,
            'notificationsCount' => AdminPortal::notificationCount($user),
            'election' => $election,
            'statistics' => $statistics,
            'turnoutSections' => $turnoutSections,
            'report' => $report,
            'exportUrls' => $exportUrls,
            'winningParty' => $winningParty,
        ]);
    }

    public function talent(Request $request): View
    {
        $user = $request->user()->loadCount('passkeys');

        $events = $user->isSuperAdmin()
            ? TalentEvent::query()
                ->withCount(['entries', 'votes'])
                ->with('entries:id,talent_event_id,status')
                ->orderByDesc('event_date')
                ->get()
            : $this->scope->talentEvents($user);

        $rows = $events->map(function (TalentEvent $event) {
            $entries = $event->entries;
            $approved = $entries->where('status', TalentEventEntry::STATUS_APPROVED)->count();
            $rejected = $entries->where('status', TalentEventEntry::STATUS_REJECTED)->count();
            $pending = $entries->where('status', TalentEventEntry::STATUS_PENDING)->count();
            $participants = $event->entries_count ?? $entries->count();
            $votes = $event->votes_count ?? 0;

            return [
                'name' => $event->title,
                'slug' => $event->slug,
                'category' => $event->talent_category?->label() ?? '—',
                'status' => $event->displayStatusLabel(),
                'participants' => $participants,
                'approved' => $approved,
                'rejected' => $rejected,
                'pending' => $pending,
                'votes' => $votes,
                'voting_method' => $event->votingMethodLabel(),
                'winners' => (int) ($event->number_of_winners ?? 3),
                'participation' => $participants > 0 ? (int) round(($approved / max($participants, 1)) * 100) : 0,
                'export_pdf' => route('admin.results.talent.export', [$event, 'format' => 'pdf']),
                'export_excel' => route('admin.results.talent.export', [$event, 'format' => 'excel']),
                'show_url' => route('admin.results.talent.show', $event),
            ];
        });

        return view('admin.reports.talent', [
            'user' => $user,
            'notificationsCount' => AdminPortal::notificationCount($user),
            'rows' => $rows,
            'totals' => [
                'events' => $rows->count(),
                'participants' => $rows->sum('participants'),
                'approved' => $rows->sum('approved'),
                'votes' => $rows->sum('votes'),
            ],
        ]);
    }

    public function fundraising(Request $request): View
    {
        $user = $request->user()->loadCount('passkeys');

        $fundraisers = Fundraiser::query()
            ->withCount('donations')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.reports.fundraising', [
            'user' => $user,
            'notificationsCount' => AdminPortal::notificationCount($user),
            'fundraisers' => $fundraisers,
            'summary' => [
                'campaigns' => $fundraisers->count(),
                'total_goal' => (float) $fundraisers->sum(fn (Fundraiser $f) => (float) $f->goal_amount),
                'total_raised' => (float) $fundraisers->sum(fn (Fundraiser $f) => (float) $f->amount_raised),
                'total_donations' => Donation::query()->count(),
            ],
        ]);
    }
}
