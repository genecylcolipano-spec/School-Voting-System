<?php

namespace Tests\Feature\Auth;

use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        SystemSetting::setValue('enable_student_registration', true);

        $this->get('/register')
            ->assertOk()
            ->assertSee('Create portal account');
    }

    public function test_registration_requires_roster_fields_not_password(): void
    {
        SystemSetting::setValue('enable_student_registration', true);

        $this->from('/register')
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertSessionHasErrors(['account_id', 'first_name', 'last_name']);

        $this->assertGuest();
    }
}
