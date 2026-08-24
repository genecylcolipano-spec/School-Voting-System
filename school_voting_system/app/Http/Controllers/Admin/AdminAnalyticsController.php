<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminAnalyticsService;
use App\Services\Admin\AdminScopeService;
use App\Support\AdminPortal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAnalyticsController extends Controller
{
    public function __construct(
        protected AdminAnalyticsService $analytics,
        protected AdminScopeService $scope,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user()->load(['staffRole', 'passkeys']);
        $elections = $this->scope->reportableElections($user, preferClosed: false);
        $election = $this->scope->resolveReportElection($user, $this->requestedElectionId($request), preferClosed: false);
        $report = $this->analytics->fullReport($user, $election);

        return view('admin.analytics.index', [
            'user' => $user,
            'notificationsCount' => AdminPortal::notificationCount($user),
            'report' => $report,
            'election' => $election,
            'elections' => $elections,
        ]);
    }

    public function live(Request $request): JsonResponse
    {
        $user = $request->user();
        $election = $this->scope->resolveReportElection($user, $this->requestedElectionId($request), preferClosed: false);
        $report = $this->analytics->fullReport($user, $election);
        $report['updated_at'] = now()->toIso8601String();

        return response()->json($report);
    }

    protected function requestedElectionId(Request $request): ?int
    {
        $value = $request->query('election');

        return filled($value) ? (int) $value : null;
    }
}
