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
