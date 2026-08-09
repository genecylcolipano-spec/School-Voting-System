<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ManagesPortalNotifications;
use App\Http\Controllers\Controller;
use App\Models\PortalNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminNotificationController extends Controller
{
    use ManagesPortalNotifications;

    public function index(Request $request): View
    {
        return $this->notificationIndexView(
            $request,
            'admin.notifications.index',
            route('admin.notifications.index'),
        );
    }

    public function feed(Request $request): JsonResponse
    {
        return $this->feedJson($request);
    }

    public function markRead(Request $request): RedirectResponse|JsonResponse
    {
        return $this->markAllNotificationsRead($request);
    }

    public function markOne(Request $request, PortalNotification $notification): RedirectResponse|JsonResponse
    {
        return $this->markNotificationRead($request, $notification);
    }

    public function destroy(Request $request, PortalNotification $notification): RedirectResponse
    {
        return $this->destroyNotification($request, $notification);
    }
}
