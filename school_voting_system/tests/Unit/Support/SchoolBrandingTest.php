<?php

namespace Tests\Unit\Support;

use App\Models\SystemSetting;
use App\Support\SchoolBranding;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
    }
}
