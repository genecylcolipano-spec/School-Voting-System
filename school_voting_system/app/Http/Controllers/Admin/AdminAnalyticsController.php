<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PasskeyRecoveryRequest;
use App\Services\Admin\AdminAnalyticsService;
use App\Support\AdminPortal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAnalyticsController extends Controller
{
    public function __construct(protected AdminAnalyticsService $analytics) {}

    public function index(Request $request): View
    {
        $user = $request->user()->load(['staffRole', 'passkeys']);
        $report = $this->analytics->fullReport($user);

        return view('admin.analytics.index', [
            'user' => $user,
            'notificationsCount' => $user->isSuperAdmin() ? AdminPortal::recoveryCount() : 0,
            'report' => $report,
        ]);
    }

    public function live(Request $request): JsonResponse
    {
        $report = $this->analytics->fullReport($request->user());
        $report['updated_at'] = now()->toIso8601String();

        return response()->json($report);
    }
}
