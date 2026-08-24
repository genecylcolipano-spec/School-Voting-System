<?php

namespace Tests\Feature\Portal;

use App\Enums\AnnouncementAudience;
use App\Enums\AnnouncementRelatedModule;
use App\Enums\AnnouncementStatus;
use App\Enums\ElectionStatus;
use App\Models\Announcement;
use App\Models\AnnouncementAttachment;
use App\Models\Election;
use App\Models\User;
use App\Services\Portal\AnnouncementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnnouncementCommunicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_plus_grade_only_notifies_that_grade(): void
    {
        $gradeEleven = User::factory()->create(['grade_level' => '11']);
        $gradeTwelve = User::factory()->create(['grade_level' => '12']);
        $announcement = $this->makeAnnouncement([
            'target_audiences' => [
                AnnouncementAudience::Students->value,
                AnnouncementAudience::SpecificGrade->value,
            ],
            'target_grade_level' => '11',
        ]);

        $ids = app(AnnouncementService::class)->recipientQuery($announcement)->pluck('id');

        $this->assertTrue($ids->contains($gradeEleven->id));
        $this->assertFalse($ids->contains($gradeTwelve->id));
        $this->assertTrue(
            Announcement::query()->published()->visibleToUser($gradeEleven)->whereKey($announcement->id)->exists()
        );
        $this->assertFalse(
            Announcement::query()->published()->visibleToUser($gradeTwelve)->whereKey($announcement->id)->exists()
        );
    }

    public function test_results_announcement_is_published_and_links_to_student_results(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $election = Election::factory()->create([
            'status' => ElectionStatus::Closed,
            'voting_starts_at' => now()->subDays(2),
            'voting_ends_at' => now()->subHour(),
            'public_results_published' => true,
        ]);

        $announcement = app(AnnouncementService::class)->generateForResultsPublished(
            $election->title,
            AnnouncementRelatedModule::Election,
            $election->id,
            $admin,
        );

        $this->assertTrue($announcement->is_published);
        $this->assertTrue($announcement->isLive());
        $this->assertContains(AnnouncementAudience::Students->value, $announcement->target_audiences);
        $this->assertContains(AnnouncementAudience::Faculty->value, $announcement->target_audiences);
        $this->assertSame(
            route('student.results.election.show', $election),
            $announcement->relatedRecordUrl(\App\Enums\UserRole::Student),
        );
        $this->assertSame(
            route('faculty.results.election.show', $election),
            $announcement->relatedRecordUrl(\App\Enums\UserRole::Faculty),
        );
        $this->assertFalse($announcement->notify_in_app);
    }

    public function test_results_announcement_is_reused_on_republish_and_hidden_when_unpublished(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $faculty = User::factory()->faculty()->create();
        $election = Election::factory()->create([
            'status' => ElectionStatus::Closed,
            'voting_starts_at' => now()->subDays(2),
            'voting_ends_at' => now()->subHour(),
            'public_results_published' => true,
        ]);

        $service = app(AnnouncementService::class);
        $first = $service->generateForResultsPublished(
            $election->title,
            AnnouncementRelatedModule::Election,
            $election->id,
            $admin,
        );
        $second = $service->generateForResultsPublished(
            $election->title,
            AnnouncementRelatedModule::Election,
            $election->id,
            $admin,
        );

        $this->assertSame($first->id, $second->id);
        $this->assertSame(
            1,
            Announcement::query()
                ->where('auto_source_type', 'results_published')
                ->where('auto_source_id', $election->id)
                ->where('related_module', AnnouncementRelatedModule::Election)
                ->count()
        );
        $this->assertTrue(
            Announcement::query()->published()->visibleToUser($faculty)->whereKey($first->id)->exists()
        );

        $service->retractResultsPublished(
            AnnouncementRelatedModule::Election,
            $election->id,
            $admin,
        );

        $this->assertFalse($first->fresh()->isLive());
        $this->assertFalse(
            Announcement::query()->published()->visibleToUser($faculty)->whereKey($first->id)->exists()
        );

        $restored = $service->generateForResultsPublished(
            $election->title,
            AnnouncementRelatedModule::Election,
            $election->id,
            $admin,
        );

        $this->assertSame($first->id, $restored->id);
        $this->assertTrue($restored->isLive());
    }

    public function test_scheduled_announcement_notifies_after_publish_time(): void
    {
        $student = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $announcement = $this->makeAnnouncement([
            'created_by' => $admin->id,
            'published_at' => now()->subMinute(),
            'notifications_sent_count' => 0,
            'notify_in_app' => true,
        ]);

        Artisan::call('portal:process-scheduled-announcements');

        $this->assertDatabaseHas('portal_notifications', [
            'user_id' => $student->id,
            'type' => 'student_announcement',
            'announcement_id' => $announcement->id,
        ]);
        $this->assertGreaterThan(0, $announcement->fresh()->notifications_sent_count);
    }

    public function test_student_cannot_download_attachment_outside_audience(): void
    {
        Storage::fake('public');
        $student = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $announcement = $this->makeAnnouncement([
            'target_audiences' => [AnnouncementAudience::Faculty->value],
            'created_by' => $admin->id,
        ]);

        $path = 'announcements/attachments/secret.pdf';
        Storage::disk('public')->put($path, 'pdf-bytes');

        $attachment = AnnouncementAttachment::query()->create([
            'announcement_id' => $announcement->id,
            'original_name' => 'secret.pdf',
            'path' => $path,
            'mime_type' => 'application/pdf',
            'size_bytes' => 9,
            'uploaded_by' => $admin->id,
        ]);

        $this->actingAs($student)
            ->get(route('student.announcements.attachments.download', [$announcement, $attachment]))
            ->assertForbidden();
    }

    public function test_archiving_does_not_keep_the_post_live(): void
    {
        $payload = app(AnnouncementService::class)->payloadFromValidated([
            'title' => 'Archived Notice',
            'status' => AnnouncementStatus::Archived->value,
            'is_published' => true,
            'target_audiences' => [AnnouncementAudience::Students->value],
        ]);

        $this->assertFalse($payload['is_published']);
        $this->assertSame(AnnouncementStatus::Archived->value, $payload['status']);
    }

    public function test_regular_admin_cannot_edit_another_admins_announcement(): void
    {
        $owner = User::factory()->admin()->create();
        $other = User::factory()->admin()->create();
        $announcement = $this->makeAnnouncement(['created_by' => $owner->id]);

        $this->actingAs($other)
            ->get(route('admin.announcements.edit', $announcement))
            ->assertForbidden();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeAnnouncement(array $overrides = []): Announcement
    {
        return Announcement::query()->create(array_merge([
            'title' => 'Campus Notice',
            'slug' => 'campus-notice-'.Str::random(6),
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
            'created_by' => User::factory()->admin()->create()->id,
        ], $overrides));
    }
}
