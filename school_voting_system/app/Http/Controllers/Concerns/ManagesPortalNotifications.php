<?php

namespace App\Http\Controllers\Concerns;

use App\Models\PortalNotification;
use App\Services\Portal\PortalNotificationService;
use App\Support\AdminPortal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

trait ManagesPortalNotifications
{
    protected function notificationService(): PortalNotificationService
    {
        return app(PortalNotificationService::class);
    }

    protected function notificationIndexView(Request $request, string $view, string $indexRoute): View
    {
        $user = $request->user()->loadCount('passkeys');
        $search = $request->string('search')->toString() ?: null;
        $status = $request->string('status')->toString() ?: null;
        $period = $request->string('period')->toString() ?: null;
        $isAdmin = $request->routeIs('admin.*');
        $isFaculty = $request->routeIs('faculty.*');

        return view($view, [
            'user' => $user,
            'notificationsCount' => AdminPortal::notificationCount($user),
            'notifications' => $this->notificationService()
                ->paginateForUser($user, $search, $status, $period)
                ->through(fn (PortalNotification $notification) => [
                    'model' => $notification,
                    'icon' => $this->notificationService()->iconForType(
                        $notification->type,
                        $notification->module,
                    ),
                    'url' => $this->notificationService()->actionUrlFor($notification),
                ]),
            'filters' => [
                'search' => $search ?? '',
                'status' => $status ?? '',
                'period' => $period ?? '',
            ],
            'unreadCount' => $this->notificationService()->unreadCountFor($user),
            'indexRoute' => $indexRoute,
            'markAllRoute' => match (true) {
                $isAdmin => route('admin.notifications.read'),
                $isFaculty => route('faculty.notifications.read'),
                default => route('student.notifications.read'),
            },
            'markOneRouteName' => match (true) {
                $isAdmin => 'admin.notifications.read-one',
                $isFaculty => 'faculty.notifications.read-one',
                default => 'student.notifications.read-one',
            },
            'deleteRouteName' => match (true) {
                $isAdmin => 'admin.notifications.destroy',
                $isFaculty => 'faculty.notifications.destroy',
                default => 'student.notifications.destroy',
            },
            'theme' => match (true) {
                $isAdmin => 'admin',
                $isFaculty => 'faculty',
                default => 'student',
            },
        ]);
    }

    public function feedJson(Request $request): JsonResponse
    {
        return response()->json(
            $this->notificationService()->feedForUser($request->user()),
        );
    }

    public function markNotificationRead(Request $request, PortalNotification $notification): RedirectResponse|JsonResponse
    {
        $this->notificationService()->markRead($request->user(), $notification);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => true]);
        }

        return back();
    }

    public function markAllNotificationsRead(Request $request): RedirectResponse|JsonResponse
    {
        $this->notificationService()->markAllReadFor($request->user());

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Notifications marked as read.');
    }

    public function destroyNotification(Request $request, PortalNotification $notification): RedirectResponse
    {
        $this->notificationService()->deleteForUser($request->user(), $notification);

        return back()->with('success', 'Notification deleted.');
    }
}
