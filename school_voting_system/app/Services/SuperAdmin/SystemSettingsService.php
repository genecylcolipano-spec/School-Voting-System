<?php

namespace App\Services\SuperAdmin;

use App\Models\SystemSetting;
use App\Support\SchoolBranding;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SystemSettingsService
{
    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return [
            'system_name' => SystemSetting::getValue('system_name', 'School Voting System'),
            'school_name' => SystemSetting::getValue('school_name', 'Rosemont Hills Montessori College'),
            'school_logo_path' => SystemSetting::getValue('school_logo_path'),
            'academic_year' => SystemSetting::getValue('academic_year', now()->format('Y').'-'.now()->addYear()->format('Y')),
            'semester' => SystemSetting::getValue('semester', '1st Semester'),
            'enable_student_registration' => (bool) SystemSetting::getValue('enable_student_registration', true),
            'enable_faculty_registration' => (bool) SystemSetting::getValue('enable_faculty_registration', false),
            'passwordless_auth_enabled' => true,
            'enable_elections' => (bool) SystemSetting::getValue('enable_elections', true),
            'enable_talent_voting' => (bool) SystemSetting::getValue('enable_talent_voting', true),
            'enable_fundraising' => (bool) SystemSetting::getValue('enable_fundraising', true),
            'announcement_default_visibility' => SystemSetting::getValue('announcement_default_visibility', 'all'),
            'announcement_default_expiration_days' => (int) SystemSetting::getValue('announcement_default_expiration_days', 14),
            'session_timeout_minutes' => (int) SystemSetting::getValue('session_timeout_minutes', 30),
            'ip_whitelist_enabled' => (bool) SystemSetting::getValue('ip_whitelist_enabled', false),
            'ip_whitelist' => SystemSetting::getValue('ip_whitelist', ['127.0.0.1', '::1']),
            'two_factor_recovery_enabled' => (bool) SystemSetting::getValue('two_factor_recovery_enabled', true),
            'public_results_published' => (bool) SystemSetting::getValue('public_results_published', false),
            'support_email' => SystemSetting::getValue('support_email', config('mail.from.address', 'ictsupport@school.edu')),
            'support_team_label' => SystemSetting::getValue('support_team_label', 'ICT Support Team'),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(array $validated, ?UploadedFile $logo = null, bool $removeLogo = false): void
    {
        $stringKeys = [
            'system_name',
            'school_name',
            'academic_year',
            'semester',
            'announcement_default_visibility',
            'support_email',
            'support_team_label',
        ];

        foreach ($stringKeys as $key) {
            if (array_key_exists($key, $validated)) {
                SystemSetting::setValue($key, (string) ($validated[$key] ?? ''), 'string');
            }
        }

        $booleanKeys = [
            'enable_student_registration',
            'enable_faculty_registration',
            'enable_elections',
            'enable_talent_voting',
            'enable_fundraising',
            'ip_whitelist_enabled',
            'two_factor_recovery_enabled',
            'public_results_published',
        ];

        foreach ($booleanKeys as $key) {
            if (array_key_exists($key, $validated)) {
                SystemSetting::setValue($key, (bool) $validated[$key], 'boolean');
            }
        }

        if (isset($validated['announcement_default_expiration_days'])) {
            SystemSetting::setValue(
                'announcement_default_expiration_days',
                (int) $validated['announcement_default_expiration_days'],
                'integer',
            );
        }

        if (isset($validated['session_timeout_minutes'])) {
            SystemSetting::setValue(
                'session_timeout_minutes',
                (int) $validated['session_timeout_minutes'],
                'integer',
            );
        }

        if (array_key_exists('ip_whitelist', $validated)) {
            $ips = is_array($validated['ip_whitelist'])
                ? $validated['ip_whitelist']
                : array_values(array_filter(array_map('trim', explode(',', (string) $validated['ip_whitelist']))));
            SystemSetting::setValue('ip_whitelist', $ips, 'json');
        }

        if ($removeLogo) {
            $existing = SystemSetting::getValue('school_logo_path');
            if (filled($existing)) {
                Storage::disk('public')->delete((string) $existing);
            }
            SystemSetting::setValue('school_logo_path', '', 'string');
        }

        if ($logo) {
            $existing = SystemSetting::getValue('school_logo_path');
            if (filled($existing)) {
                Storage::disk('public')->delete((string) $existing);
            }
            $path = $logo->store('school-logos', 'public');
            SystemSetting::setValue('school_logo_path', $path, 'string');
        }
    }

    public function schoolLogoUrl(): ?string
    {
        // Settings editor shows only the uploaded logo, not the public fallback crest.
        return SchoolBranding::logoUrl(withFallback: false);
    }
}
