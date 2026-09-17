<?php

namespace App\Support;

/**
 * Detects in-app browsers (Messenger, Facebook, Instagram, …) that cannot run WebAuthn.
 */
class InAppBrowser
{
    /**
     * @return array{blocked: bool, android: bool, ios: bool, name: ?string}
     */
    public static function detect(?string $userAgent): array
    {
        $ua = (string) $userAgent;
        $android = (bool) preg_match('/Android/i', $ua);
        $ios = (bool) preg_match('/iPhone|iPad|iPod/i', $ua);

        $name = self::appName($ua);

        if ($name === null && $android && preg_match('/; wv\)/i', $ua)) {
            $name = 'in-app browser';
        }

        return [
            'blocked' => $name !== null,
            'android' => $android,
            'ios' => $ios,
            'name' => $name,
        ];
    }

    public static function chromeIntentUrl(string $url): string
    {
        $parts = parse_url($url) ?: [];
        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $path = $parts['path'] ?? '/';
        $query = isset($parts['query']) ? '?'.$parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#'.$parts['fragment'] : '';

        return 'intent://'.$host.$port.$path.$query.$fragment
            .'#Intent;scheme='.$scheme.';package=com.android.chrome;S.browser_fallback_url='
            .rawurlencode($url).';end';
    }

    public static function chromeSchemeUrl(string $url): string
    {
        return 'googlechrome://navigate?url='.rawurlencode($url);
    }

    protected static function appName(string $ua): ?string
    {
        return match (true) {
            (bool) preg_match('/FBAN\/Messenger|MessengerForiOS|MessengerLite/i', $ua) => 'Messenger',
            (bool) preg_match('/FBAN|FBAV|FB_IAB|FB4A|FBIOS|IABMV/i', $ua) => 'Facebook',
            (bool) preg_match('/Instagram/i', $ua) => 'Instagram',
            (bool) preg_match('/BytedanceWebview|TikTok|musical_ly/i', $ua) => 'TikTok',
            (bool) preg_match('/ Line\//i', $ua) => 'LINE',
            (bool) preg_match('/Snapchat/i', $ua) => 'Snapchat',
            (bool) preg_match('/WhatsApp/i', $ua) => 'WhatsApp',
            (bool) preg_match('/MicroMessenger/i', $ua) => 'WeChat',
            (bool) preg_match('/LinkedInApp/i', $ua) => 'LinkedIn',
            (bool) preg_match('/Twitter/i', $ua) => 'X',
            (bool) preg_match('/Pinterest/i', $ua) => 'Pinterest',
            (bool) preg_match('/KAKAOTALK/i', $ua) => 'KakaoTalk',
            default => null,
        };
    }
}
