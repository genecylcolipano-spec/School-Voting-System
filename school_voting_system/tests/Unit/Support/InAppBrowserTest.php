<?php

namespace Tests\Unit\Support;

use App\Support\InAppBrowser;
use PHPUnit\Framework\TestCase;

class InAppBrowserTest extends TestCase
{
    public function test_chrome_and_safari_are_allowed(): void
    {
        $chrome = InAppBrowser::detect('Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 Chrome/120.0.0.0 Mobile Safari/537.36');
        $safari = InAppBrowser::detect('Mozilla/5.0 (iPhone; CPU iPhone OS 17_4 like Mac OS X) AppleWebKit/605.1.15 Version/17.4 Mobile/15E148 Safari/604.1');

        $this->assertFalse($chrome['blocked']);
        $this->assertFalse($safari['blocked']);
    }

    public function test_messenger_ios_is_blocked(): void
    {
        $detected = InAppBrowser::detect('Mozilla/5.0 (iPhone; CPU iPhone OS 17_4 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 [FBAN/MessengerForiOS;FBAV/192.0.0.1]');

        $this->assertTrue($detected['blocked']);
        $this->assertTrue($detected['ios']);
        $this->assertSame('Messenger', $detected['name']);
    }

    public function test_facebook_android_is_blocked(): void
    {
        $detected = InAppBrowser::detect('Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 Chrome/120.0.0.0 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/10.0.0.1]');

        $this->assertTrue($detected['blocked']);
        $this->assertTrue($detected['android']);
        $this->assertSame('Facebook', $detected['name']);
    }

    public function test_instagram_and_tiktok_are_blocked(): void
    {
        $this->assertTrue(InAppBrowser::detect('Mozilla/5.0 Instagram 192.0.0.1.123 Android')['blocked']);
        $this->assertTrue(InAppBrowser::detect('Mozilla/5.0 (Linux; Android 13) BytedanceWebview TikTok')['blocked']);
    }

    public function test_android_webview_without_chrome_is_blocked(): void
    {
        $detected = InAppBrowser::detect('Mozilla/5.0 (Linux; Android 13; Pixel 7; wv) AppleWebKit/537.36 Version/4.0 Chrome/120.0.0.0 Mobile Safari/537.36');

        $this->assertTrue($detected['blocked']);
        $this->assertSame('in-app browser', $detected['name']);
    }

    public function test_chrome_intent_url_targets_android_chrome(): void
    {
        $intent = InAppBrowser::chromeIntentUrl('https://schoolvotingsystem.online/');

        $this->assertStringStartsWith('intent://schoolvotingsystem.online/#Intent;', $intent);
        $this->assertStringContainsString('package=com.android.chrome', $intent);
        $this->assertStringContainsString('scheme=https', $intent);
    }
}
