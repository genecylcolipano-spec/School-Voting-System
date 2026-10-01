<?php

namespace Tests\Feature\Talent;

use App\Enums\TalentEventStatus;
use App\Enums\TalentVotingMethod;
use App\Models\Election;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\User;
use App\Services\Media\TalentVideoStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TalentVideoStreamTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(TalentVideoStorage::DISK);
        Storage::fake(TalentVideoStorage::FALLBACK_DISK);
    }

    public function test_admin_can_watch_and_download_a_video_stored_on_the_private_disk(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $entry = $this->makeEntryWithVideo();

        $this->actingAs($admin)
            ->get(route('talent.video.stream', $entry))
            ->assertOk()
            ->assertHeaderContains('content-disposition', 'inline');

        $this->actingAs($admin)
            ->get(route('talent.video.stream', ['entry' => $entry, 'download' => 1]))
            ->assertOk()
            ->assertHeaderContains('content-disposition', 'attachment');
    }

    public function test_watch_and_download_return_not_found_when_the_file_is_missing(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $entry = $this->makeEntryWithVideo(putFile: false);

        $this->actingAs($admin)
            ->get(route('talent.video.stream', $entry))
            ->assertNotFound();

        $this->actingAs($admin)
            ->get(route('talent.video.stream', ['entry' => $entry, 'download' => 1]))
            ->assertNotFound();
    }

    public function test_student_can_watch_an_approved_published_upload(): void
    {
        $student = User::factory()->create();
        $entry = $this->makeEntryWithVideo();

        $this->actingAs($student)
            ->get(route('talent.video.stream', $entry))
            ->assertOk();

        $this->actingAs($student)
            ->postJson(route('student.talent-voting.view', $entry))
            ->assertOk()
            ->assertJson([
                'watched' => true,
                'entry_id' => $entry->id,
                'file' => route('talent.video.stream', $entry),
                'embed' => null,
            ]);
    }

    public function test_student_can_watch_their_own_pending_upload(): void
    {
        $student = User::factory()->create();
        $entry = $this->makeEntryWithVideo([
            'user_id' => $student->id,
            'status' => TalentEventEntry::STATUS_PENDING,
        ]);

        $this->actingAs($student)
            ->get(route('talent.video.stream', $entry))
            ->assertOk();
    }

    public function test_student_cannot_watch_another_students_unpublished_upload(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $entry = $this->makeEntryWithVideo([
            'user_id' => $owner->id,
            'status' => TalentEventEntry::STATUS_PENDING,
        ]);

        $this->actingAs($viewer)
            ->get(route('talent.video.stream', $entry))
            ->assertNotFound();
    }

    public function test_legacy_local_disk_videos_still_stream(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $path = TalentVideoStorage::DIRECTORY.'/legacy.mp4';
        Storage::disk(TalentVideoStorage::FALLBACK_DISK)->put($path, 'legacy-video');

        $entry = $this->makeEntryWithVideo(putFile: false, path: $path);

        $this->actingAs($admin)
            ->get(route('talent.video.stream', $entry))
            ->assertOk();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeEntryWithVideo(array $overrides = [], bool $putFile = true, ?string $path = null): TalentEventEntry
    {
        $path ??= TalentVideoStorage::DIRECTORY.'/performance.mp4';

        if ($putFile) {
            Storage::disk(TalentVideoStorage::DISK)->put($path, 'performance-bytes');
        }

        $event = TalentEvent::query()->create([
            'election_id' => Election::factory()->create()->id,
            'title' => 'ROSEMONT IDOL',
            'slug' => 'rosemont-idol-'.uniqid(),
            'event_date' => now()->addDay(),
            'venue' => 'Auditorium',
            'status' => TalentEventStatus::VotingOpen,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'voting_starts_at' => now()->subHour(),
            'voting_ends_at' => now()->addDay(),
            'published_to_students' => true,
            'created_by' => User::factory()->admin()->create()->id,
        ]);

        return TalentEventEntry::query()->create(array_merge([
            'talent_event_id' => $event->id,
            'display_name' => 'Genecyl Colipano',
            'performance_title' => 'Ikaw At Ako',
            'status' => TalentEventEntry::STATUS_APPROVED,
            'source' => TalentEventEntry::SOURCE_ADMIN,
            'video_path' => $path,
        ], $overrides));
    }
}
