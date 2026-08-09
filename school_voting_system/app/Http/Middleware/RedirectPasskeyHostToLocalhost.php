<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * WebAuthn passkeys are bound to the relying party ID "localhost".
 * Browsers treat localhost, 127.0.0.1, and [::1] as different origins,
 * so logins from mismatched hosts often send credentials the server does not recognize.
 */
class RedirectPasskeyHostToLocalhost
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        if (! in_array($host, ['127.0.0.1', '[::1]', '::1'], true)) {
            return $next($request);
        }

        $port = $request->getPort();
        $portSuffix = in_array($port, [80, 443], true) ? '' : ':'.$port;

        return redirect()->to(
            $request->getScheme().'://localhost'.$portSuffix.$request->getRequestUri(),
            302
        );
    }
}
