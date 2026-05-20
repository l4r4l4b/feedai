<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Tourist locale resolution. Single source of truth for "what language is
 * the viewer seeing this feed in" across the public surface (feed, pay,
 * contact). Priority chain:
 *
 *  1. Cookie `feedai_locale` — explicit choice via switcher
 *  2. Query `?lang=` — link sharing / QR codes
 *  3. Accept-Language header — browser default
 *  4. Vendor's source locale — last-resort fallback
 *
 * Anything outside the supported set falls through to the next layer.
 */
class Locale
{
    /** @var list<string> */
    public const SUPPORTED = ['en', 'de', 'th'];

    public const COOKIE_NAME = 'feedai_locale';

    public static function resolve(Request $request, ?string $vendorLocale = null): string
    {
        $cookie = (string) $request->cookie(self::COOKIE_NAME, '');
        if (self::isSupported($cookie)) {
            return $cookie;
        }

        $query = (string) $request->query('lang', '');
        if (self::isSupported($query)) {
            return $query;
        }

        // getPreferredLanguage returns $locales[0] when nothing matches —
        // that would shadow the vendor fallback. Walk the actual header
        // language list and intersect with SUPPORTED.
        foreach ($request->getLanguages() as $candidate) {
            $primary = strtolower(substr((string) $candidate, 0, 2));
            if (self::isSupported($primary)) {
                return $primary;
            }
        }

        return self::isSupported((string) $vendorLocale)
            ? (string) $vendorLocale
            : 'en';
    }

    public static function isSupported(string $locale): bool
    {
        return in_array($locale, self::SUPPORTED, true);
    }
}
