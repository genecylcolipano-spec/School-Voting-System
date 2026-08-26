<?php

namespace Tests\Feature\Admin;

use App\Enums\AnnouncementAudience;
use App\Enums\AnnouncementStatus;
use App\Models\Announcement;
use App\Models\PortalNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommunicationPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_communication_pages_use_unread_notification_count(): void
    {
        $super = User::factory()->superAdmin()->create();
        $announcement = $this->makeAnnouncement($super, 'Campus Broadcast');

        PortalNotification::query()->create([
            'title' => 'Comms ping',
            'message' => 'Unread for communication',
            'type' => 'info',
            'user_id' => $super->id,
            'recipient_role' => 'super_admin',
        ]);

        $this->actingAs($super)
            ->get(route('admin.announcements.index'))
            ->assertOk()
            ->assertSee('data-initial-count="1"', false)
            ->assertSee('Campus Broadcast')
            ->assertSee(route('admin.announcements.edit', $announcement), false);

        $this->actingAs($super)
            ->get(route('admin.announcements.create'))
            ->assertOk()
            ->assertSee('data-initial-count="1"', false);

        $html = $this->actingAs($super)
            ->withSession(['success' => 'Unique announcement flash'])
            ->get(route('admin.announcements.edit', $announcement))
            ->assertOk()
            ->assertSee('data-initial-count="1"', false)
            ->getContent();

        $this->assertSame(1, substr_count($html, 'Unique announcement flash'));

        $notificationsHtml = $this->actingAs($super)
            ->withSession(['success' => 'Unique inbox flash'])
            ->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee('data-initial-count="1"', false)
            ->getContent();

        $this->assertSame(1, substr_count($notificationsHtml, 'Unique inbox flash'));
    }

    public function test_super_admin_sees_every_announcement_and_regular_admin_sees_own_only(): void
    {
        $super = User::factory()->superAdmin()->create();
        $admin = User::factory()->admin()->create();
        $other = User::factory()->admin()->create();

        $this->makeAnnouncement($admin, 'My Campus Notice');
        $this->makeAnnouncement($other, 'Other Campus Notice');

        $this->actingAs($super)
            ->get(route('admin.announcements.index'))
            ->assertOk()
            ->assertSee('My Campus Notice')
            ->assertSee('Other Campus Notice');

        $this->actingAs($admin)
            ->get(route('admin.announcements.index'))
            ->assertOk()
            ->assertSee('My Campus Notice')
            ->assertDontSee('Other Campus Notice');
    }

    public function test_empty_announcements_offer_a_create_link(): void
    {
        $super = User::factory()->superAdmin()->create();

        $this->actingAs($super)
            ->get(route('admin.announcements.index'))
            ->assertOk()
            ->assertSee('No announcements yet.')
            ->assertSee('Create your first announcement')
            ->assertSee(route('admin.announcements.create'), false);
    }

    protected function makeAnnouncement(User $author, string $title): Announcement
    {
        return Announcement::query()->create([
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(6),
            'summary' => 'Summary',
            'body' => 'Body',
            'category' => 'general',
            'priority' => 'normal',
            'target_audiences' => [AnnouncementAudience::Students->value],
            'status' => AnnouncementStatus::Published->value,
            'is_published' => true,
            'published_at' => now()->subMinute(),
            'notify_in_app' => true,
            'send_email' => false,
            'created_by' => $author->id,
        ]);
    }
}
