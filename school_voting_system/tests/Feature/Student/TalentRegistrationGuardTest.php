<?php

namespace Tests\Feature\Student;

use App\Enums\TalentCategory;
use App\Enums\TalentEventStatus;
use App\Enums\TalentRegistrationMethod;
use App\Enums\TalentVotingMethod;
use App\Models\Election;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\User;
use App\Services\Media\ImageCompressionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\Support\TestImageFactory;
use Tests\TestCase;

class TalentRegistrationGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_oversized_thumbnail_is_accepted_and_compressed_without_the_two_megabyte_message(): void
    {
        Storage::fake('public');

        $student = User::factory()->create();
        $event = $this->makeOpenCompetition();
        $thumbnail = TestImageFactory::jpegUploadedFileReportingKilobytes(3000, 'thumbnail.jpg');

        $this->assertGreaterThan(2048 * 1024, $thumbnail->getSize());
        $this->assertLessThanOrEqual(ImageCompressionService::MAX_UPLOAD_KILOBYTES * 1024, $thumbnail->getSize());

        $this->actingAs($student)
            ->from(route('student.talent-registration.register', $event))
            ->post(route('student.talent-registration.review.store', $event), $this->validPayload([
                'thumbnail' => $thumbnail,
            ]))
            ->assertRedirect(route('student.talent-registration.review', $event))
            ->assertSessionDoesntHaveErrors();

        $draft = session('talent_registration_draft.'.$event->id);
        $this->assertIsArray($draft);
        $path = $draft['files']['thumbnail']['path'] ?? null;
        $this->assertNotEmpty($path);
        $this->assertTrue(Storage::disk('public')->exists($path));

        if (extension_loaded('gd')) {
            $this->assertLessThanOrEqual(
                ImageCompressionService::MAX_STORED_BYTES,
                Storage::disk('public')->size($path),
            );
        }

        $this->actingAs($student)
            ->get(route('student.talent-registration.register', $event))
            ->assertOk()
            ->assertDontSee('must not be greater than 2048 kilobytes')
            ->assertSee('compressed automatically');
    }

    public function test_registration_form_prefills_school_record_and_ignores_posted_identity(): void
    {
        $student = User::factory()->create([
            'name' => 'Genecyl Colipano',
            'account_id' => '2026-00421',
            'grade_level' => '12',
            'section' => 'A',
            'course' => 'BSIT',
        ]);
        $event = $this->makeOpenCompetition();

        $this->actingAs($student)
            ->get(route('student.talent-registration.register', $event))
            ->assertOk()
            ->assertSee('value="Genecyl Colipano"', false)
            ->assertSee('value="2026-00421"', false)
            ->assertSee('value="12"', false)
            ->assertSee('value="A"', false)
            ->assertSee('value="BSIT"', false);

        $this->actingAs($student)
            ->from(route('student.talent-registration.register', $event))
            ->post(route('student.talent-registration.review.store', $event), $this->validPayload([
                'display_name' => 'Someone Else',
                'student_id_number' => 'FAKE-ID',
                'grade_level' => '11',
                'section' => 'Z',
                'course_strand' => 'STEM',
            ]))
            ->assertRedirect(route('student.talent-registration.review', $event));

        $fields = session('talent_registration_draft.'.$event->id)['fields'] ?? [];
        $this->assertSame('Genecyl Colipano', $fields['display_name'] ?? null);
        $this->assertSame('2026-00421', $fields['student_id_number'] ?? null);
        $this->assertSame('12', $fields['grade_level'] ?? null);
        $this->assertSame('A', $fields['section'] ?? null);
        $this->assertSame('BSIT', $fields['course_strand'] ?? null);
    }

    public function test_closed_registration_blocks_the_form_review_and_final_submit(): void
    {
        $student = User::factory()->create();
        $event = $this->makeOpenCompetition();

        $this->actingAs($student)
            ->from(route('student.talent-registration.register', $event))
            ->post(route('student.talent-registration.review.store', $event), $this->validPayload())
            ->assertRedirect(route('student.talent-registration.review', $event));

        $event->forceFill([
            'registration_ends_at' => now(),
            'submission_deadline' => now(),
        ])->save();

        $this->assertFalse($event->fresh()->isRegistrationOpen());

        $this->actingAs($student)
            ->get(route('student.talent-registration.register', $event))
            ->assertRedirect(route('student.talent-registration.show', $event))
            ->assertSessionHas('error', 'Registration Closed. Registration is not available right now.');

        $this->actingAs($student)
            ->from(route('student.talent-registration.register', $event))
            ->post(route('student.talent-registration.review.store', $event), $this->validPayload([
                'student_id_number' => 'SID-CLOSED-REVIEW',
            ]))
            ->assertRedirect(route('student.talent-registration.show', $event))
            ->assertSessionHas('error');

        $this->actingAs($student)
            ->from(route('student.talent-registration.review', $event))
            ->post(route('student.talent-registration.store', $event), [
                'confirm' => '1',
            ])
            ->assertRedirect(route('student.talent-registration.show', $event))
            ->assertSessionHas('error');

        $this->assertFalse(
            TalentEventEntry::query()
                ->where('talent_event_id', $event->id)
                ->where('user_id', $student->id)
                ->exists()
        );
    }

    public function test_close_registration_is_effective_at_the_same_second(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-29 11:30:00'));

        $event = $this->makeOpenCompetition([
            'registration_ends_at' => now()->addDays(7),
            'submission_deadline' => now()->addDays(7),
        ]);

        $this->assertTrue($event->isRegistrationOpen());

        $event->forceFill([
            'registration_ends_at' => now(),
            'submission_deadline' => now(),
        ])->save();

        $this->assertFalse($event->fresh()->isRegistrationOpen());
        $this->assertTrue($event->fresh()->registrationHasClosed());

        Carbon::setTestNow();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'display_name' => 'Genecyl Colipano',
            'student_id_number' => 'SID-'.uniqid(),
            'grade_level' => '12',
            'section' => 'A',
            'course_strand' => 'STEM',
            'talent_category' => TalentCategory::OpenTalent->value,
            'performance_title' => 'Campus Anthem',
            'performance_description' => 'A short vocal performance for Rosemont Idol.',
            'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeOpenCompetition(array $overrides = []): TalentEvent
    {
        $election = Election::factory()->create();

        return TalentEvent::query()->create(array_merge([
            'election_id' => $election->id,
            'title' => 'ROSEMONT IDOL',
            'slug' => 'rosemont-idol-'.uniqid(),
            'event_date' => now()->addDays(10),
            'venue' => 'Auditorium',
            'status' => TalentEventStatus::EntriesOpen,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'registration_method' => TalentRegistrationMethod::Both,
            'registration_starts_at' => now()->subHour(),
            'registration_ends_at' => now()->addDays(3),
            'voting_starts_at' => now()->addDays(8),
            'voting_ends_at' => now()->addDays(10),
            'published_to_students' => true,
            'created_by' => User::factory()->admin()->create()->id,
        ], $overrides));
    }
}
