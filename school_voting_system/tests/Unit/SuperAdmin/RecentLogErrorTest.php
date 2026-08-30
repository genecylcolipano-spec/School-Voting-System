<?php

namespace Tests\Unit\SuperAdmin;

use App\Services\SuperAdmin\SuperAdminDashboardService;
use Tests\TestCase;

class RecentLogErrorTest extends TestCase
{
    public function test_stale_errors_are_not_treated_as_current_health(): void
    {
        $path = storage_path('logs/recent-error-test.log');
        $stale = now()->subHour()->format('Y-m-d H:i:s');

        file_put_contents($path, "[{$stale}] local.ERROR: Undefined variable \$recoveryRequests\n");

        try {
            $this->assertNull(app(SuperAdminDashboardService::class)->recentLogErrorFrom($path));
        } finally {
            @unlink($path);
        }
    }

    public function test_recent_application_errors_are_surfaced(): void
    {
        $path = storage_path('logs/recent-error-test.log');
        $recent = now()->subMinutes(2)->format('Y-m-d H:i:s');

        file_put_contents($path, "[{$recent}] local.ERROR: Payment gateway timeout\n");

        try {
            $this->assertStringContainsString(
                'Payment gateway timeout',
                (string) app(SuperAdminDashboardService::class)->recentLogErrorFrom($path),
            );
        } finally {
            @unlink($path);
        }
    }

    public function test_testing_channel_errors_are_ignored(): void
    {
        $path = storage_path('logs/recent-error-test.log');
        $recent = now()->subMinute()->format('Y-m-d H:i:s');

        file_put_contents($path, "[{$recent}] testing.ERROR: Factory exploded\n");

        try {
            $this->assertNull(app(SuperAdminDashboardService::class)->recentLogErrorFrom($path));
        } finally {
            @unlink($path);
        }
    }
}
