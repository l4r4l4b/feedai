<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\Locale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Sets the feedai_locale cookie and bounces the visitor back where they
 * came from. Used by the top-of-feed locale chips. Plain HTTP GET so it
 * works without JavaScript and survives any Alpine-load issues in the
 * public layout.
 */
class SetLocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        if (! Locale::isSupported($locale)) {
            abort(404);
        }

        $back = $request->headers->get('referer') ?: url('/');
        // Strip ?lang= from the referer so the cookie wins on the next render
        // and we don't keep an explicit query around.
        $back = preg_replace('/([?&])lang=[^&]*&?/', '$1', $back);
        $back = rtrim((string) $back, '?&');

        $cookie = Cookie::create(
            name: Locale::COOKIE_NAME,
            value: $locale,
            expire: time() + 60 * 60 * 24 * 180,
            path: '/',
            secure: $request->isSecure(),
            httpOnly: false,
            sameSite: Cookie::SAMESITE_LAX,
        );

        return redirect()->away($back)->withCookie($cookie);
    }
}
