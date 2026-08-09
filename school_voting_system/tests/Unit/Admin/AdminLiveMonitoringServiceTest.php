<?php

namespace Tests\Unit\Admin;

use App\Enums\ElectionStatus;
use App\Enums\UserRole;
use App\Models\Election;
use App\Models\TalentEvent;
use App\Models\User;
use App\Services\Admin\AdminLiveMonitoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLiveMonitoringServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_sees_all_elections_as_monitoring_cards(): void
    {
        $super = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        Election::factory()->create([
            'title' => 'Campus Election A',
            'created_by' => $admin->id,
            'status' => ElectionStatus::Draft,
        ]);

        $cards = app(AdminLiveMonitoringService::class)->electionCards($super);

        $this->assertTrue($cards->contains(fn (array $card) => $card['name'] === 'Campus Election A'));
        $this->assertSame($admin->name, $cards->firstWhere('name', 'Campus Election A')['owner_name']);
    }

    public function test_admin_only_sees_created_or_assigned_elections(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $other = User::factory()->create(['role' => UserRole::Admin]);

        Election::factory()->create([
            'title' => 'Mine',
            'created_by' => $admin->id,
            'status' => ElectionStatus::Draft,
        ]);

        Election::factory()->create([
            'title' => 'Theirs',
            'created_by' => $other->id,
            'status' => ElectionStatus::Draft,
        ]);

        $cards = app(AdminLiveMonitoringService::class)->electionCards($admin);

        $this->assertTrue($cards->contains(fn (array $card) => $card['name'] === 'Mine'));
        $this->assertFalse($cards->contains(fn (array $card) => $card['name'] === 'Theirs'));
    }
}
