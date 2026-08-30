<?php

namespace Tests\Unit\Support;

use App\Models\SystemSetting;
use App\Support\SchoolBranding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SchoolBrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_reads_system_and_school_names(): void
    {
        SystemSetting::setValue('system_name', 'School Voting System', 'string');
        SystemSetting::setValue('school_name', 'Rosemont Hills Montessori College Inc.', 'string');
        SystemSetting::setValue('academic_year', '2026-2027', 'string');
        SystemSetting::setValue('semester', '1st Semester', 'string');

        $this->assertSame('School Voting System', SchoolBranding::systemName());
        $this->assertSame('Rosemont Hills Montessori College Inc.', SchoolBranding::schoolName());
        $this->assertSame('Powered by Rosemont Hills Montessori College Inc.', SchoolBranding::poweredBy());
        $this->assertSame('2026-2027', SchoolBranding::academicYear());
        $this->assertSame('1st Semester', SchoolBranding::semester());
        $this->assertSame('2026-2027 · 1st Semester', SchoolBranding::periodLabel());
    }

    public function test_logo_url_is_null_without_upload_when_fallback_disabled(): void
    {
        SystemSetting::setValue('school_logo_path', '', 'string');

        $this->assertNull(SchoolBranding::logoUrl(withFallback: false));
        $this->assertNull(SchoolBranding::logoIssue());
    }

    public function test_logo_url_cache_busts_when_file_exists(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('school-logos/seal.png', 'fake-image');
        SystemSetting::setValue('school_logo_path', 'school-logos/seal.png', 'string');

        $url = SchoolBranding::logoUrl(withFallback: false);

        $this->assertNotNull($url);
        $this->assertStringContainsString('school-logos/seal.png', $url);
        $this->assertStringContainsString('?v=', $url);
        $this->assertNull(SchoolBranding::logoIssue());
    }

    public function test_logo_issue_reports_missing_file(): void
    {
        Storage::fake('public');
        SystemSetting::setValue('school_logo_path', 'school-logos/gone.png', 'string');

        $this->assertNull(SchoolBranding::logoUrl(withFallback: false));
        $this->assertSame('missing_file', SchoolBranding::logoIssue());
    }
}
