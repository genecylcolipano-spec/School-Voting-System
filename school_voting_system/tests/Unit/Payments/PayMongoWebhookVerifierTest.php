<?php

namespace Tests\Unit\Payments;

use App\Services\Payments\PayMongoWebhookVerifier;
use Tests\TestCase;

class PayMongoWebhookVerifierTest extends TestCase
{
    public function test_accepts_matching_test_signature_within_tolerance(): void
    {
        $secret = 'whsk_test_secret';
        $payload = '{"data":{"type":"event"}}';
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        $ok = (new PayMongoWebhookVerifier)->verify(
            $payload,
            't='.$timestamp.',te='.$signature.',li=',
            $secret,
        );

        $this->assertTrue($ok);
    }

    public function test_rejects_tampered_payload(): void
    {
        $secret = 'whsk_test_secret';
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $timestamp.'.{"ok":true}', $secret);

        $ok = (new PayMongoWebhookVerifier)->verify(
            '{"ok":false}',
            't='.$timestamp.',te='.$signature.',li=',
            $secret,
        );

        $this->assertFalse($ok);
    }
}
