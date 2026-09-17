<?php

namespace Tests\Feature\Auth;

use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_shows_branding_and_passkey_action(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Secure Passkey Portal')
            ->assertSee('Sign in with Passkey')
            ->assertSee("Don't have an account?", false)
            ->assertSee('Create one')
            ->assertSee('Forgot your passkey?')
            ->assertSee('Recover access')
            ->assertSee(route('register'), false)
            ->assertSee(route('login.recovery'), false)
            ->assertSee(\App\Support\SchoolBranding::periodLabel(), false)
            ->assertSee(\App\Support\SchoolBranding::poweredBy(), false)
            ->assertSee('favicon-svs.png', false)
            ->assertSee('theme-color', false);
    }

    public function test_messenger_in_app_browser_hides_passkey_and_asks_to_open_safari(): void
    {
        $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_4 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 [FBAN/MessengerForiOS;FBAV/192.0.0.1]',
        ])
            ->get(route('login'))
            ->assertOk()
            ->assertSee('cannot use passkeys')
            ->assertSee('Open in Safari')
            ->assertSee('Copy link')
            ->assertSee('id="passkey-supported-panel"', false);
    }

    public function test_android_facebook_browser_offers_chrome_intent(): void
    {
        $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 Chrome/120.0.0.0 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/10.0.0.1]',
        ])
            ->get(route('login'))
            ->assertOk()
            ->assertSee('cannot use passkeys')
            ->assertSee('Open in Chrome')
            ->assertSee('intent://', false)
            ->assertSee('package=com.android.chrome', false)
            ->assertDontSee('Open in Safari');
    }

    public function test_login_page_shows_flash_error_and_status(): void
    {
        $this->withSession(['error' => 'The enrollment link is no longer valid.'])
            ->get(route('login'))
            ->assertOk()
            ->assertSee('The enrollment link is no longer valid.');

        $this->withSession(['status' => 'Student registration is currently disabled by the school administrator.'])
            ->get(route('login'))
            ->assertOk()
            ->assertSee('Student registration is currently disabled by the school administrator.');
    }

    public function test_create_account_link_is_hidden_when_registration_is_disabled(): void
    {
        SystemSetting::setValue('enable_student_registration', false, 'boolean');

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Create one')
            ->assertSee('Forgot your passkey?')
            ->assertSee('Recover access');
    }

    public function test_recover_access_link_is_hidden_when_recovery_is_disabled(): void
    {
        SystemSetting::setValue('two_factor_recovery_enabled', false, 'boolean');

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Recover access')
            ->assertDontSee(route('login.recovery'), false)
            ->assertSee('Create one');
    }
}
