<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Auth\RoleRedirectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class FacultyRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_faculty_role_helpers_and_redirect(): void
    {
        $faculty = User::factory()->faculty()->create();

        $this->assertTrue($faculty->isFaculty());
        $this->assertFalse($faculty->canVote());
        $this->assertSame('Faculty', $faculty->roleLabel());
        $this->assertSame('/faculty/dashboard', app(RoleRedirectService::class)->dashboardPathFor($faculty));
        $this->assertSame(UserRole::Faculty, $faculty->role);
    }

    public function test_faculty_can_view_dashboard_without_breeze_nav(): void
    {
        $faculty = User::factory()->faculty()->create(['name' => 'Faculty Tester']);

        $response = $this->actingAs($faculty)->get(route('faculty.dashboard'));

        $response->assertOk();
        $response->assertSee('Welcome back, Faculty Tester');
        $response->assertSee('Faculty Portal');
        $response->assertDontSee('bg-white border-b border-gray-100', false);
    }

    public function test_super_admin_can_open_faculty_list(): void
    {
        $super = User::factory()->superAdmin()->create();
        User::factory()->faculty()->create(['account_id' => 'FACULTY-100']);

        $this->actingAs($super)
            ->get(route('super-admin.faculty.index'))
            ->assertOk()
            ->assertSee('FACULTY-100');
    }

    public function test_logged_in_super_admin_can_open_enrollment_link(): void
    {
        $super = User::factory()->superAdmin()->create();
        $faculty = User::factory()->faculty()->create(['account_id' => 'FACULTY-861']);

        $url = URL::temporarySignedRoute(
            'register.passkey.bootstrap',
            now()->addMinutes(120),
            ['user' => $faculty->id],
        );

        $response = $this->actingAs($super)->get($url);

        $response->assertOk();
        $response->assertSee('Register your passkey');
        $response->assertSee('FACULTY-861');
        $this->assertGuest();
    }

    public function test_faculty_settings_hides_grade_and_section(): void
    {
        $faculty = User::factory()->faculty()->create([
            'account_id' => 'FACULTY-861',
            'is_active' => true,
            'grade_level' => '10',
            'section' => 'A',
        ]);

        $response = $this->actingAs($faculty)->get(route('profile.edit', ['section' => 'profile']));

        $response->assertOk();
        $response->assertSee('Assigned Competitions');
        $response->assertDontSee('Grade / Section');
        $response->assertDontSee('Grade and section are managed by the school administration.');
    }
}
