<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self)');

        if (! $response->headers->has('Content-Security-Policy')) {

            if ($request->is('docs/*') || $request->is('docs/api*')) {
                // CSP pour docs (Swagger / Scramble)
                $response->headers->set(
                    'Content-Security-Policy',
                    "default-src 'self'; " .
                    "script-src 'self' https://unpkg.com 'unsafe-inline'; " .
                    "style-src 'self' https://unpkg.com 'unsafe-inline'; " .
                    "frame-ancestors 'none'; object-src 'none';"
                );

            } else {
                // CSP principale (app Laravel + Vite + fonts)
                $response->headers->set(
                    'Content-Security-Policy',
                    "default-src 'self'; " .
                    "base-uri 'self'; " .
                    "frame-ancestors 'none'; " .
                    "object-src 'none'; " .
                    "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
                    "style-src 'self' 'unsafe-inline' https://fonts.bunny.net; " .
                    "font-src 'self' https://fonts.bunny.net; " .
                    "img-src 'self' data:; " .
                    "connect-src 'self';"
                );
            }
        }

        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        return $response;
    }
}
