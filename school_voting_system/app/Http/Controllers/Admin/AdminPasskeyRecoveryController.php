<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DismissPasskeyRecoveryRequest;
use App\Http\Requests\Admin\EnrollPasskeyRecoveryRequest;
use App\Models\PasskeyRecoveryRequest;
use App\Services\Auth\PasskeyRecoveryQueueService;
use App\Support\AdminPortal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdminPasskeyRecoveryController extends Controller
{
    public function __construct(
        protected PasskeyRecoveryQueueService $queue,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $user = $request->user()->loadCount('passkeys');

        return view('admin.recovery.index', [
            'user' => $user,
            'notificationsCount' => AdminPortal::notificationCount($user),
            'recoveryRequests' => $this->queue->pendingQueue($user),
        ]);
    }

    public function enroll(EnrollPasskeyRecoveryRequest $request, PasskeyRecoveryRequest $recoveryRequest): JsonResponse|RedirectResponse
    {
        try {
            $result = $this->queue->issueEnrollment($recoveryRequest, $request->user());
        } catch (HttpException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], $exception->getStatusCode());
            }

            return back()->with('error', $exception->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Enrollment link generated.',
                'enrollment_url' => $result['url'],
                'expires_in_minutes' => $result['expires_in_minutes'],
                'email_sent' => $result['email_sent'],
                'email_error' => $result['email_error'],
                'recipient' => $result['recipient'],
            ]);
        }

        $message = $result['email_sent']
            ? 'Enrollment link emailed to '.$result['recipient'].'.'
            : 'Enrollment link generated. Copy it below if email delivery failed.';

        $redirect = back()
            ->with('success', $message)
            ->with('enrollment_url', $result['url']);

        if ($result['email_error'] && ! $result['email_sent']) {
            $redirect = $redirect->with('error', $result['email_error']);
        }

        return $redirect;
    }

    public function dismiss(DismissPasskeyRecoveryRequest $request, PasskeyRecoveryRequest $recoveryRequest): JsonResponse|RedirectResponse
    {
        try {
            $this->queue->dismiss($recoveryRequest, $request->user());
        } catch (HttpException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], $exception->getStatusCode());
            }

            return back()->with('error', $exception->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Recovery request dismissed.']);
        }

        return back()->with('success', 'Recovery request dismissed.');
    }
}
