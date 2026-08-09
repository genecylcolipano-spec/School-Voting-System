<?php

namespace App\Support;

use App\Models\PasskeyRecoveryRequest;
use App\Models\PortalNotification;
use App\Models\User;
use App\Services\Auth\RoleRedirectService;
use App\Services\Portal\PortalNotificationService;
use Illuminate\Http\Request;

class AdminPortal
{
    public static function recoveryCount(): int
    {
        return PasskeyRecoveryRequest::query()
            ->where('status', PasskeyRecoveryRequest::STATUS_PENDING)
            ->count();
    }

    public static function notificationCount(User $user): int
    {
        return app(PortalNotificationService::class)->unreadCountFor($user);
    }

    /**
     * @return array{user: User, notificationsCount: int}
     */
    public static function layoutData(Request $request): array
    {
        $user = $request->user()->loadCount('passkeys');

        return [
            'user' => $user,
            'notificationsCount' => self::notificationCount($user),
        ];
    }

    public static function dashboardRouteName(User $user): string
    {
        return app(RoleRedirectService::class)->routeNameFor($user);
    }
}
