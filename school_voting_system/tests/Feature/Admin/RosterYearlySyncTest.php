<?php

namespace Tests\Feature\Admin;

use App\Enums\RosterRegistrationStatus;
use App\Enums\StudentStatus;
use App\Models\AllowedStudent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class RosterYearlySyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_yearly_sync_page(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('super-admin.roster.students.year-sync'))
            ->assertOk()
            ->assertSee('Yearly student roster sync')
            ->assertSee('Archive students missing from this file');
    }

    public function test_regular_admin_cannot_use_yearly_sync(): void
    {
        $admin = User::factory()->admin()->create();
        $csv = "account_id,first_name,last_name,grade_level,section\nSTU-NEW,No,Access,10,A\n";

        $this->actingAs($admin)
            ->get(route('super-admin.roster.students.year-sync'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('super-admin.roster.students.year-sync.preview'), [
                'school_year' => '2026-2027',
                'csv_file' => $this->csvUpload($csv),
                'archive_missing' => '1',
            ])
            ->assertForbidden();
    }

    public function test_yearly_sync_updates_registered_students_and_archives_missing(): void
    {
        $admin = User::factory()->superAdmin()->create();

        AllowedStudent::query()->create([
            'account_id' => 'STU-KEEP',
            'first_name' => 'Returning',
            'last_name' => 'Student',
            'grade_level' => '10',
            'section' => 'A',
            'school_year' => '2025-2026',
            'is_registered' => true,
            'registration_status' => RosterRegistrationStatus::Registered,
        ]);
        $returning = User::factory()->create([
            'account_id' => 'STU-KEEP',
            'name' => 'Returning Student',
            'email' => 'returning@example.com',
            'grade_level' => '10',
            'section' => 'A',
            'school_year' => '2025-2026',
            'student_status' => StudentStatus::Enrolled,
        ]);

        AllowedStudent::query()->create([
            'account_id' => 'STU-GRAD',
            'first_name' => 'Graduating',
            'last_name' => 'Senior',
            'grade_level' => '12',
            'section' => 'A',
            'is_registered' => true,
            'registration_status' => RosterRegistrationStatus::Registered,
        ]);
        $graduate = User::factory()->create([
            'account_id' => 'STU-GRAD',
            'name' => 'Graduating Senior',
            'email' => 'graduate@example.com',
            'grade_level' => '12',
            'section' => 'A',
            'student_status' => StudentStatus::Enrolled,
        ]);

        $csv = "account_id,first_name,last_name,grade_level,section\n"
            ."STU-KEEP,Returning,Student,11,B\n"
            ."STU-FRESH,New,Enrollee,7,C\n";

        $this->actingAs($admin)
            ->post(route('super-admin.roster.students.year-sync.preview'), [
                'school_year' => '2026-2027',
                'csv_file' => $this->csvUpload($csv),
                'archive_missing' => '1',
            ])
            ->assertRedirect(route('super-admin.roster.students.year-sync'))
            ->assertSessionHas('success');

        $this->actingAs($admin)
            ->get(route('super-admin.roster.students.year-sync'))
            ->assertOk()
            ->assertSee('STU-KEEP')
            ->assertSee('STU-GRAD')
            ->assertSee('STU-FRESH');

        $this->actingAs($admin)
            ->post(route('super-admin.roster.students.year-sync.apply'))
            ->assertRedirect(route('super-admin.roster.students.index'));

        $keepRoster = AllowedStudent::query()->where('account_id', 'STU-KEEP')->first();
        $this->assertSame('11', $keepRoster?->grade_level);
        $this->assertSame('B', $keepRoster?->section);
        $this->assertSame('2026-2027', $keepRoster?->school_year);
        $this->assertNull($keepRoster?->archived_at);
        $this->assertTrue($keepRoster?->is_registered);

        $keepUser = $returning->fresh();
        $this->assertSame($returning->id, $keepUser?->id);
        $this->assertSame('returning@example.com', $keepUser?->email);
        $this->assertSame('11', $keepUser?->grade_level);
        $this->assertSame('B', $keepUser?->section);
        $this->assertSame('2026-2027', $keepUser?->school_year);
        $this->assertTrue($keepUser?->is_active);
        $this->assertNull($keepUser?->archived_at);

        $gradRoster = AllowedStudent::query()->where('account_id', 'STU-GRAD')->first();
        $this->assertNotNull($gradRoster?->archived_at);
        $this->assertNotNull($graduate->fresh()->archived_at);
        $this->assertFalse($graduate->fresh()->is_active);
        $this->assertSame('graduate@example.com', $graduate->fresh()->email);

        $fresh = AllowedStudent::query()->where('account_id', 'STU-FRESH')->first();
        $this->assertNotNull($fresh);
        $this->assertFalse($fresh->is_registered);
        $this->assertSame('7', $fresh->grade_level);
        $this->assertSame('2026-2027', $fresh->school_year);
        $this->assertNull(User::findByAccountId('STU-FRESH'));
    }

    public function test_yearly_sync_can_skip_archiving_missing_students(): void
    {
        $admin = User::factory()->superAdmin()->create();
        AllowedStudent::query()->create([
            'account_id' => 'STU-STAY',
            'first_name' => 'Stay',
            'last_name' => 'Here',
            'grade_level' => '9',
            'section' => 'A',
        ]);

        $csv = "account_id,first_name,last_name,grade_level,section\nSTU-ONLY,Only,Row,8,B\n";

        $this->actingAs($admin)
            ->post(route('super-admin.roster.students.year-sync.preview'), [
                'school_year' => '2026-2027',
                'csv_file' => $this->csvUpload($csv),
            ])
            ->assertRedirect(route('super-admin.roster.students.year-sync'));

        $this->actingAs($admin)
            ->post(route('super-admin.roster.students.year-sync.apply'))
            ->assertRedirect(route('super-admin.roster.students.index'));

        $this->assertNull(AllowedStudent::query()->where('account_id', 'STU-STAY')->first()?->archived_at);
        $this->assertNotNull(AllowedStudent::query()->where('account_id', 'STU-ONLY')->first());
    }

    public function test_yearly_sync_restores_a_returning_archived_student(): void
    {
        $admin = User::factory()->superAdmin()->create();
        AllowedStudent::query()->create([
            'account_id' => 'STU-BACK',
            'first_name' => 'Back',
            'last_name' => 'Again',
            'grade_level' => '10',
            'section' => 'A',
            'is_registered' => true,
            'registration_status' => RosterRegistrationStatus::Registered,
            'archived_at' => now(),
        ]);
        $user = User::factory()->create([
            'account_id' => 'STU-BACK',
            'name' => 'Back Again',
            'grade_level' => '10',
            'section' => 'A',
            'is_active' => false,
            'archived_at' => now(),
            'student_status' => StudentStatus::Withdrawn,
        ]);

        $csv = "account_id,first_name,last_name,grade_level,section\nSTU-BACK,Back,Again,11,B\n";

        $this->actingAs($admin)
            ->post(route('super-admin.roster.students.year-sync.preview'), [
                'school_year' => '2026-2027',
                'csv_file' => $this->csvUpload($csv),
                'archive_missing' => '1',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('super-admin.roster.students.year-sync.apply'))
            ->assertRedirect(route('super-admin.roster.students.index'));

        $this->assertNull(AllowedStudent::query()->where('account_id', 'STU-BACK')->first()?->archived_at);
        $this->assertNull($user->fresh()->archived_at);
        $this->assertTrue($user->fresh()->is_active);
        $this->assertSame(StudentStatus::Enrolled, $user->fresh()->student_status);
        $this->assertSame('11', $user->fresh()->grade_level);
    }

    protected function csvUpload(string $contents, string $filename = 'year-sync.csv'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'year-sync').'.csv';
        file_put_contents($path, $contents);

        return new UploadedFile($path, $filename, 'text/csv', null, true);
    }
}
