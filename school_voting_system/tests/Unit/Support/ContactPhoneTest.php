<?php

namespace Tests\Unit\Support;

use App\Support\ContactPhone;
use PHPUnit\Framework\TestCase;

class ContactPhoneTest extends TestCase
{
    public function test_normalize_trims_and_clears_blank_values(): void
    {
        $this->assertSame('+639171234567', ContactPhone::normalize('  +639171234567  '));
        $this->assertNull(ContactPhone::normalize(''));
        $this->assertNull(ContactPhone::normalize('   '));
        $this->assertNull(ContactPhone::normalize(null));
    }

    public function test_tel_href_keeps_digits_and_plus(): void
    {
        $this->assertSame('tel:+639171234567', ContactPhone::telHref('+63 917 123 4567'));
        $this->assertNull(ContactPhone::telHref(''));
    }

    public function test_to_e164_accepts_common_ph_formats(): void
    {
        $this->assertSame('+639171234567', ContactPhone::toE164('0917 123 4567'));
        $this->assertSame('+639171234567', ContactPhone::toE164('9171234567'));
        $this->assertSame('+639171234567', ContactPhone::toE164('639171234567'));
        $this->assertNull(ContactPhone::toE164('n/a'));
    }

    public function test_mask_hides_the_middle_digits(): void
    {
        $this->assertSame('+639••••••567', ContactPhone::mask('09171234567'));
    }

    public function test_redact_urls_omits_enrollment_links_from_logs(): void
    {
        $this->assertSame(
            'Setup [link omitted]',
            ContactPhone::redactUrls('Setup http://localhost/e/abc123xyz')
        );
    }
}
