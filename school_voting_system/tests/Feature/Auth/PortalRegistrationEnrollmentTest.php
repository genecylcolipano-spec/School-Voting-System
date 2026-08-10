<?php

namespace Tests\Feature\Auth;

use App\Enums\RosterRegistrationStatus;
use App\Mail\RosterPasskeyEnrollmentMail;
use App\Models\AllowedAdministrator;
use App\Models\AllowedFaculty;
use App\Models\AllowedStudent;
use App\Models\EnrollmentToken;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Auth\EnrollmentTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PortalRegistrationEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        SystemSetting::setValue('enable_student_registration', true);
    }

    public function test_register_screen_shows_confirm_and_validate(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Confirm &amp; Validate', false);
    }

    public function test_roster_import_style_record_stays_not_registered_without_email(): void
    {
        Mail::fake();

        $student = AllowedStudent::query()->create([
            'account_id' => 'STU-100',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'grade_level' => 'Grade 10',
            'section' => 'A',
            'is_registered' => false,
            'registration_status' => RosterRegistrationStatus::NotRegistered,
        ]);

        Mail::assertNothingSent();
        $this->assertFalse($student->fresh()->is_registered);
        $this->assertSame(RosterRegistrationStatus::NotRegistered, $student->fresh()->registrationStatus());
    }

    public function test_valid_confirm_and_validate_issues_token_and_email(): void
    {
        Mail::fake();

        AllowedStudent::query()->create([
            'account_id' => 'STU-200',
            'first_name' => 'Alex',
            'last_name' => 'Rivera',
            'grade_level' => 'Grade 11',
            'section' => 'B',
            'is_registered' => false,
        ]);

        $response = $this->post(route('register.store'), [
            'account_id' => 'STU-200',
            'first_name' => 'Alex',
            'last_name' => 'Rivera',
            'email' => 'alex@example.com',
        ]);

        $response->assertRedirect(route('register.verified'));

        $student = AllowedStudent::query()->where('account_id', 'STU-200')->first();
        $this->assertTrue($student->isEnrollmentPending());
        $this->assertFalse($student->is_registered);

        $this->assertDatabaseHas('enrollment_tokens', [
            'account_id' => 'STU-200',
            'email' => 'alex@example.com',
        ]);

        Mail::assertSent(RosterPasskeyEnrollmentMail::class);
        $this->assertNull(User::query()->where('account_id', 'STU-200')->first());
    }

    public function test_invalid_roster_does_not_create_token_or_email(): void
    {
        Mail::fake();

        AllowedStudent::query()->create([
            'account_id' => 'STU-300',
            'first_name' => 'Pat',
            'last_name' => 'Lee',
            'is_registered' => false,
        ]);

        $this->from(route('register'))
            ->post(route('register.store'), [
                'account_id' => 'STU-300',
                'first_name' => 'Wrong',
                'last_name' => 'Name',
                'email' => 'wrong@example.com',
            ])
            ->assertSessionHasErrors('account_id');

        $this->assertDatabaseCount('enrollment_tokens', 0);
        Mail::assertNothingSent();
    }

    public function test_three_failed_validations_block_further_attempts(): void
    {
        RateLimiter::clear('registration-validate:'.sha1('127.0.0.1|stu-400'));

        AllowedStudent::query()->create([
            'account_id' => 'STU-400',
            'first_name' => 'Sam',
            'last_name' => 'Kim',
            'is_registered' => false,
        ]);

        foreach (range(1, 3) as $attempt) {
            $this->from(route('register'))
                ->post(route('register.store'), [
                    'account_id' => 'STU-400',
                    'first_name' => 'Nope',
                    'last_name' => 'Nope',
                    'email' => "fail{$attempt}@example.com",
                ])
                ->assertSessionHasErrors('account_id');
        }

        $this->from(route('register'))
            ->post(route('register.store'), [
                'account_id' => 'STU-400',
                'first_name' => 'Sam',
                'last_name' => 'Kim',
                'email' => 'sam@example.com',
            ])
            ->assertSessionHasErrors('account_id');

        $this->assertStringContainsString(
            'Too many unsuccessful registration attempts',
            session('errors')->first('account_id'),
        );
    }

    public function test_expired_enroll_token_shows_expired_page(): void
    {
        $student = AllowedStudent::query()->create([
            'account_id' => 'STU-500',
            'first_name' => 'Casey',
            'last_name' => 'Ng',
            'is_registered' => false,
            'registration_status' => RosterRegistrationStatus::EnrollmentPending,
        ]);

        $plain = 'ExpiredTokenValue12345678901234567890123456789012';
        EnrollmentToken::query()->create([
            'token_hash' => hash('sha256', $plain),
            'roster_type' => 'student',
            'roster_id' => $student->id,
            'account_id' => 'STU-500',
            'email' => 'casey@example.com',
            'first_name' => 'Casey',
            'last_name' => 'Ng',
            'role' => 'student',
            'payload' => [],
            'expires_at' => now()->subHour(),
        ]);

        $this->get(route('register.enroll', ['token' => $plain]))
            ->assertRedirect(route('register.expired'));

        $this->get(route('register.expired'))
            ->assertOk()
            ->assertSee('Enrollment Link Expired')
            ->assertSee('Create Account Again');
    }

    public function test_new_token_invalidates_previous_token(): void
    {
        Mail::fake();

        AllowedStudent::query()->create([
            'account_id' => 'STU-600',
            'first_name' => 'Riley',
            'last_name' => 'Cruz',
            'is_registered' => false,
        ]);

        $this->post(route('register.store'), [
            'account_id' => 'STU-600',
            'first_name' => 'Riley',
            'last_name' => 'Cruz',
            'email' => 'riley1@example.com',
        ])->assertRedirect(route('register.verified'));

        $first = EnrollmentToken::query()->latest('id')->first();
        $firstPlain = session(EnrollmentTokenService::SESSION_PLAIN_TOKEN);

        $this->post(route('register.store'), [
            'account_id' => 'STU-600',
            'first_name' => 'Riley',
            'last_name' => 'Cruz',
            'email' => 'riley2@example.com',
        ])->assertRedirect(route('register.verified'));

        $this->assertNotNull($first->fresh()->invalidated_at);
        $this->get(route('register.enroll', ['token' => $firstPlain]))
            ->assertRedirect(route('register.expired'));
    }

    public function test_enroll_page_loads_for_usable_token_without_creating_user(): void
    {
        Mail::fake();

        AllowedFaculty::query()->create([
            'account_id' => 'FAC-700',
            'first_name' => 'Morgan',
            'last_name' => 'Blake',
            'department' => 'Science',
            'is_registered' => false,
        ]);

        $this->post(route('register.store'), [
            'account_id' => 'FAC-700',
            'first_name' => 'Morgan',
            'last_name' => 'Blake',
            'email' => 'morgan@example.com',
        ])->assertRedirect(route('register.verified'));

        $plain = session(EnrollmentTokenService::SESSION_PLAIN_TOKEN);

        $this->get(route('register.enroll', ['token' => $plain]))
            ->assertOk()
            ->assertSee('passkey', false);

        $this->assertNull(User::query()->where('account_id', 'FAC-700')->first());
        $this->assertTrue(AllowedFaculty::query()->where('account_id', 'FAC-700')->first()->isEnrollmentPending());
    }

    public function test_admin_roster_validation_does_not_allow_role_tampering(): void
    {
        Mail::fake();

        AllowedAdministrator::query()->create([
            'account_id' => 'ADMIN-800',
            'first_name' => 'Taylor',
            'last_name' => 'Brooks',
            'department' => 'Office',
            'is_registered' => false,
        ]);

        $this->post(route('register.store'), [
            'account_id' => 'ADMIN-800',
            'first_name' => 'Taylor',
            'last_name' => 'Brooks',
            'email' => 'taylor@example.com',
        ])->assertRedirect(route('register.verified'));

        $token = EnrollmentToken::query()->where('account_id', 'ADMIN-800')->first();
        $this->assertSame('admin', $token->role->value);
        $this->assertSame('administrator', $token->roster_type);
    }
}
