<?php

namespace Tests\Feature\Admin;

use App\Mail\PasskeyResetEnrollmentLinkMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EnrollmentSmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_passkey_emails_an_enrollment_link(): void
    {
        Mail::fake();

        $admin = User::factory()->superAdmin()->create();
        $student = User::factory()->create([
            'phone' => '09171234567',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.students.show', $student))
            ->post(route('admin.passkey.reset', $student))
            ->assertRedirect()
            ->assertSessionHas('enrollment_url')
            ->assertSessionMissing('enrollment_sms');

        Mail::assertSent(PasskeyResetEnrollmentLinkMail::class);
        $this->assertDatabaseCount('passkey_enrollment_links', 1);
    }

    public function test_self_service_recovery_emails_an_enrollment_link(): void
    {
        Mail::fake();

        $student = User::factory()->create([
            'account_id' => '2026-8802',
            'email' => 'student@example.com',
            'phone' => '09179876543',
        ]);

        $this->postJson(route('login.recovery.request'), [
            'account_id' => $student->account_id,
            'email' => $student->email,
        ])->assertOk();

        Mail::assertSent(PasskeyResetEnrollmentLinkMail::class);
    }

    public function test_short_enrollment_token_opens_passkey_setup(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $faculty = User::factory()->faculty()->create([
            'account_id' => 'FACULTY-880',
            'phone' => '09170001111',
        ]);

        $this->actingAs($admin)
            ->post(route('super-admin.staff.enrollment', $faculty))
            ->assertRedirect()
            ->assertSessionHas('enrollment_url');

        $url = session('enrollment_url');
        $this->assertIsString($url);
        $this->assertStringContainsString('/e/', $url);

        $this->actingAs($admin)
            ->get($url)
            ->assertOk()
            ->assertSee('Register your passkey')
            ->assertSee('FACULTY-880')
            ->assertSee('Faculty')
            ->assertSee('e.g. School Laptop, iPhone', false)
            ->assertSee('Already have a passkey? Sign in', false);

        $this->assertGuest();
    }

    public function test_used_or_unknown_token_is_rejected(): void
    {
        $this->get(route('enroll.passkey.token', ['token' => str_repeat('a', 32)]))
            ->assertRedirect(route('login'));
    }
}
