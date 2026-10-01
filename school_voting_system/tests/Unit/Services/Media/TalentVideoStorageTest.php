<?php

namespace Tests\Unit\Services\Media;

use App\Services\Media\TalentVideoStorage;
use App\Services\Talent\StudentTalentRegistrationFlowService;
use App\Enums\TalentCategory;
use App\Enums\TalentEventStatus;
use App\Enums\TalentRegistrationMethod;
use App\Enums\TalentVotingMethod;
use App\Models\Election;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TalentVideoStorageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(TalentVideoStorage::DISK);
        Storage::fake(TalentVideoStorage::FALLBACK_DISK);
        Storage::fake('public');
    }

    public function test_private_disk_stays_local_when_no_object_storage_bucket_is_configured(): void
    {
        $this->assertSame('local', config('filesystems.disks.private.driver'));
        $this->assertSame(storage_path('app/private'), config('filesystems.disks.private.root'));
    }

    public function test_store_writes_to_the_private_disk(): void
    {
        $path = app(TalentVideoStorage::class)->store(
            UploadedFile::fake()->create('solo.mp4', 120, 'video/mp4')
        );

        $this->assertNotEmpty($path);
        $this->assertStringStartsWith(TalentVideoStorage::DIRECTORY.'/', $path);
        Storage::disk(TalentVideoStorage::DISK)->assertExists($path);
        Storage::disk(TalentVideoStorage::FALLBACK_DISK)->assertMissing($path);
    }

    public function test_exists_finds_legacy_files_on_the_local_disk(): void
    {
        $path = TalentVideoStorage::DIRECTORY.'/legacy.mp4';
        Storage::disk(TalentVideoStorage::FALLBACK_DISK)->put($path, 'legacy-bytes');

        $this->assertTrue(app(TalentVideoStorage::class)->exists($path));
    }

    public function test_delete_removes_the_file_from_both_disks(): void
    {
        $path = TalentVideoStorage::DIRECTORY.'/gone.mp4';
        Storage::disk(TalentVideoStorage::DISK)->put($path, 'new');
        Storage::disk(TalentVideoStorage::FALLBACK_DISK)->put($path, 'old');

        app(TalentVideoStorage::class)->delete($path);

        Storage::disk(TalentVideoStorage::DISK)->assertMissing($path);
        Storage::disk(TalentVideoStorage::FALLBACK_DISK)->assertMissing($path);
    }

    public function test_deleting_an_entry_removes_the_private_video(): void
    {
        $path = TalentVideoStorage::DIRECTORY.'/entry.mp4';
        Storage::disk(TalentVideoStorage::DISK)->put($path, 'bytes');

        $entry = TalentEventEntry::query()->create([
            'talent_event_id' => $this->makeCompetition()->id,
            'display_name' => 'Soloist',
            'status' => TalentEventEntry::STATUS_APPROVED,
            'source' => TalentEventEntry::SOURCE_ADMIN,
            'video_path' => $path,
        ]);

        $entry->delete();

        Storage::disk(TalentVideoStorage::DISK)->assertMissing($path);
    }

    public function test_student_registration_promotes_draft_video_onto_the_private_disk(): void
    {
        $student = User::factory()->create();
        $event = $this->makeCompetition();
        $draftPath = "talent/drafts/{$student->id}/{$event->id}/draft.mp4";
        Storage::disk(TalentVideoStorage::DISK)->put($draftPath, 'draft-video');

        $paths = app(StudentTalentRegistrationFlowService::class)->promoteDraftFiles([
            'files' => [
                'video' => [
                    'path' => $draftPath,
                    'disk' => TalentVideoStorage::DISK,
                    'name' => 'draft.mp4',
                ],
            ],
        ]);

        $this->assertNotEmpty($paths['video_path']);
        $this->assertStringStartsWith(TalentVideoStorage::DIRECTORY.'/', $paths['video_path']);
        Storage::disk(TalentVideoStorage::DISK)->assertExists($paths['video_path']);
        Storage::disk(TalentVideoStorage::DISK)->assertMissing($draftPath);
        Storage::disk(TalentVideoStorage::FALLBACK_DISK)->assertMissing($paths['video_path']);
    }

    protected function makeCompetition(): TalentEvent
    {
        return TalentEvent::query()->create([
            'election_id' => Election::factory()->create()->id,
            'title' => 'ROSEMONT IDOL',
            'slug' => 'rosemont-idol-'.uniqid(),
            'event_date' => now()->addDays(10),
            'venue' => 'Auditorium',
            'status' => TalentEventStatus::EntriesOpen,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'registration_method' => TalentRegistrationMethod::Both,
            'talent_category' => TalentCategory::OpenTalent,
            'published_to_students' => true,
            'created_by' => User::factory()->admin()->create()->id,
        ]);
    }
}
