<?php

namespace App\Services\Payments;

class PayMongoWebhookVerifier
{
    public function verify(string $rawPayload, ?string $signatureHeader, ?string $secret = null): bool
    {
        $secret = trim((string) ($secret ?? config('services.paymongo.webhook_secret')));

        if ($secret === '' || $signatureHeader === null || $signatureHeader === '') {
            return false;
        }

        $parts = $this->parseSignatureHeader($signatureHeader);
        $timestamp = $parts['t'] ?? null;

        if (! is_string($timestamp) || $timestamp === '') {
            return false;
        }

        $tolerance = (int) config('services.paymongo.webhook_tolerance', 300);
        if ($tolerance > 0 && abs(time() - (int) $timestamp) > $tolerance) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$rawPayload, $secret);
        $candidates = array_filter([
            $parts['te'] ?? null,
            $parts['li'] ?? null,
        ], fn ($value) => is_string($value) && $value !== '');

        foreach ($candidates as $candidate) {
            if (hash_equals($expected, $candidate)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, string>
     */
    protected function parseSignatureHeader(string $header): array
    {
        $parsed = [];

        foreach (explode(',', $header) as $segment) {
            $parts = explode('=', trim($segment), 2);
            if (count($parts) !== 2) {
                continue;
            }

            $parsed[trim($parts[0])] = trim($parts[1]);
        }

        return $parsed;
    }
}
