<?php

namespace Tests\Feature\Admin;

use App\Enums\RosterRegistrationStatus;
use App\Models\AllowedAdministrator;
use App\Models\AllowedFaculty;
use App\Models\AllowedStudent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RosterImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_template_can_be_downloaded_and_imported_as_a_sample(): void
    {
        Mail::fake();
        $admin = User::factory()->superAdmin()->create();

        $template = $this->actingAs($admin)
            ->get(route('super-admin.roster.students.import.template'));

        $template->assertOk();
        $csv = $template->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('account_id,first_name,last_name,grade_level,section', $csv);
        $this->assertStringContainsString('2026-00002,Maria,Santos,10,A', $csv);

        $this->actingAs($admin)
            ->post(route('super-admin.roster.students.import.store'), [
                'csv_file' => $this->csvUpload($csv, 'student-roster-template.csv'),
            ])
            ->assertRedirect(route('super-admin.roster.students.index'))
            ->assertSessionHas('success');

        $row = AllowedStudent::query()->where('account_id', '2026-00002')->first();

        $this->assertNotNull($row);
        $this->assertSame('Maria', $row->first_name);
        $this->assertSame('Santos', $row->last_name);
        $this->assertSame('10', $row->grade_level);
        $this->assertSame('A', $row->section);
        $this->assertFalse($row->is_registered);
        $this->assertNull(User::findByAccountId('2026-00002'));
        Mail::assertNothingSent();
    }

    public function test_faculty_and_administrator_templates_import_their_sample_rows(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $facultyCsv = $this->actingAs($admin)
            ->get(route('super-admin.roster.faculty.import.template'))
            ->streamedContent();

        $this->actingAs($admin)
            ->post(route('super-admin.roster.faculty.import.store'), [
                'csv_file' => $this->csvUpload($facultyCsv, 'faculty-roster-template.csv'),
            ])
            ->assertRedirect(route('super-admin.roster.faculty.index'));

        $adminCsv = $this->actingAs($admin)
            ->get(route('super-admin.roster.administrators.import.template'))
            ->streamedContent();

        $this->actingAs($admin)
            ->post(route('super-admin.roster.administrators.import.store'), [
                'csv_file' => $this->csvUpload($adminCsv, 'administrator-roster-template.csv'),
            ])
            ->assertRedirect(route('super-admin.roster.administrators.index'));

        $faculty = AllowedFaculty::query()->where('account_id', 'FAC-001')->first();
        $officer = AllowedAdministrator::query()->where('account_id', 'EMP-001')->first();

        $this->assertSame('Ana', $faculty?->first_name);
        $this->assertSame('Science', $faculty?->department);
        $this->assertSame('Jose', $officer?->first_name);
        $this->assertSame('Registrar', $officer?->department);
        $this->assertNull(User::findByAccountId('FAC-001'));
        $this->assertNull(User::findByAccountId('EMP-001'));
    }

    public function test_import_accepts_header_aliases_without_a_bom(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $csv = "student_id,firstname,lastname,grade,section\nSTU-ALIAS,Alex,Rivera,11,B\n";

        $this->actingAs($admin)
            ->post(route('super-admin.roster.students.import.store'), [
                'csv_file' => $this->csvUpload($csv),
            ])
            ->assertRedirect(route('super-admin.roster.students.index'));

        $row = AllowedStudent::query()->where('account_id', 'STU-ALIAS')->first();

        $this->assertSame('Alex', $row?->first_name);
        $this->assertSame('11', $row?->grade_level);
        $this->assertSame('B', $row?->section);
    }

    public function test_import_does_not_overwrite_a_registered_row(): void
    {
        $admin = User::factory()->superAdmin()->create();
        AllowedStudent::query()->create([
            'account_id' => 'STU-REG',
            'first_name' => 'Original',
            'last_name' => 'Name',
            'grade_level' => '10',
            'section' => 'A',
            'is_registered' => true,
            'registration_status' => RosterRegistrationStatus::Registered,
        ]);

        $csv = "account_id,first_name,last_name,grade_level,section\nSTU-REG,Changed,Person,12,Z\n";

        $this->actingAs($admin)
            ->post(route('super-admin.roster.students.import.store'), [
                'csv_file' => $this->csvUpload($csv),
            ])
            ->assertRedirect(route('super-admin.roster.students.index'))
            ->assertSessionHas('import_result');

        $row = AllowedStudent::query()->where('account_id', 'STU-REG')->first();

        $this->assertSame('Original', $row?->first_name);
        $this->assertSame('A', $row?->section);
        $this->assertTrue($row?->is_registered);
        $this->assertSame(
            'This record has already registered and cannot be overwritten.',
            session('import_result')['errors'][0]['message'] ?? null,
        );
    }

    public function test_regular_admin_cannot_download_or_import_roster_csv(): void
    {
        $admin = User::factory()->admin()->create();
        $csv = "account_id,first_name,last_name,grade_level,section\nSTU-BLOCK,No,Access,10,A\n";

        $this->actingAs($admin)
            ->get(route('super-admin.roster.students.import.template'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('super-admin.roster.students.import.store'), [
                'csv_file' => $this->csvUpload($csv),
            ])
            ->assertForbidden();

        $this->assertNull(AllowedStudent::query()->where('account_id', 'STU-BLOCK')->first());
    }

    public function test_import_page_explains_the_template_can_be_imported(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('super-admin.roster.students.import'))
            ->assertOk()
            ->assertSee('Download template CSV')
            ->assertSee('The sample row is valid and will add one roster record', false);
    }

    protected function csvUpload(string $contents, string $filename = 'roster.csv'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'roster').'.csv';
        file_put_contents($path, $contents);

        return new UploadedFile($path, $filename, 'text/csv', null, true);
    }
}
