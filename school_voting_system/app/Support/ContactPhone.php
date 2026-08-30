<?php

namespace App\Support;

class ContactPhone
{
    /**
     * @return list<string>
     */
    public static function rules(): array
    {
        return ['nullable', 'string', 'max:32'];
    }

    public static function normalize(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    public static function telHref(?string $value): ?string
    {
        $e164 = self::toE164($value);

        return $e164 ? 'tel:'.$e164 : null;
    }

    /**
     * Convert a PH-style number to E.164 (+63…). Returns null if it cannot be dialed.
     */
    public static function toE164(mixed $value): ?string
    {
        $normalized = self::normalize($value);

        if ($normalized === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $normalized) ?? '';

        if (str_starts_with($normalized, '+') && str_starts_with($digits, '63') && strlen($digits) === 12) {
            return '+'.$digits;
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return '+63'.substr($digits, 1);
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '63')) {
            return '+'.$digits;
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '9')) {
            return '+63'.$digits;
        }

        return null;
    }

    public static function mask(?string $value): ?string
    {
        $e164 = self::toE164($value);

        if ($e164 === null) {
            return null;
        }

        $visible = substr($e164, -3);

        return substr($e164, 0, 4).str_repeat('•', max(0, strlen($e164) - 7)).$visible;
    }

    public static function redactUrls(string $body): string
    {
        return (string) preg_replace('#https?://\S+#i', '[link omitted]', $body);
    }
}
