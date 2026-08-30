<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_admin_profile_page_uses_settings_tabs(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this
            ->actingAs($admin)
            ->get(route('profile.edit', ['section' => 'security']));

        $response->assertOk();
        $response->assertSee('Authentication Status');
        $response->assertSee('Logout Other Devices');
    }

    public function test_admin_settings_profile_labels_staff_role_not_department(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('profile.edit', ['section' => 'profile']))
            ->assertOk()
            ->assertSee('Staff role')
            ->assertDontSee('>Department</label>', false)
            ->assertSee('Manage your administrator profile, devices, and account security.')
            ->assertSee('Phone Number')
            ->assertDontSee('<h1 class="text-xl font-bold text-white">Settings</h1>', false);
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit', ['section' => 'profile']));

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_student_can_save_phone_number_on_settings(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Student,
            'email' => 'student@example.com',
        ]);

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '+63 917 123 4567',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit', ['section' => 'profile']));

        $this->assertSame('+63 917 123 4567', $user->refresh()->phone);
    }

    public function test_faculty_can_save_phone_number_on_settings(): void
    {
        $user = User::factory()->faculty()->create();

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '09171234567',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('09171234567', $user->refresh()->phone);
    }

    public function test_administrator_can_save_phone_number_on_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->patch('/profile', [
                'name' => $admin->name,
                'email' => $admin->email,
                'phone' => '09170000000',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit', ['section' => 'profile']));

        $this->assertSame('09170000000', $admin->refresh()->phone);
    }

    public function test_super_admin_can_save_phone_number_on_settings(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->patch('/profile', [
                'name' => $admin->name,
                'email' => $admin->email,
                'phone' => '+63 917 000 1111',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('+63 917 000 1111', $admin->refresh()->phone);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit', ['section' => 'profile']));

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account_with_confirmation(): void
    {
        $user = User::factory()->create(['role' => UserRole::Student]);

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'confirmation' => 'DELETE',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_confirmation_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create(['role' => UserRole::Student]);

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'confirmation' => 'wrong',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'confirmation')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_admin_cannot_delete_account_via_profile(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this
            ->actingAs($admin)
            ->delete('/profile', [
                'confirmation' => 'DELETE',
            ]);

        $response->assertForbidden();
        $this->assertNotNull($admin->fresh());
    }
}
