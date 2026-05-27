<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withBroadcasting(
        channels: __DIR__.'/../routes/channels.php',
        attributes: ['middleware' => ['auth:sanctum']],
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (InvalidSignatureException $exception, Request $request) {
            if (! $request->is('api/email/verify/*')) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lien de vérification invalide ou expiré.',
                ], 403);
            }

            $frontendBaseUrl = rtrim((string) (
                env('FRONTEND_URL')
                ?: config('app.url')
            ), '/');

            $query = http_build_query([
                'status' => 'error',
                'message' => 'Lien de vérification invalide ou expiré.',
            ]);

            $defaultUrl = $frontendBaseUrl.'/email-verification?'.$query;
            $template = env('FRONTEND_EMAIL_VERIFICATION_URL_TEMPLATE');

            if (! is_string($template) || trim($template) === '') {
                return redirect()->away($defaultUrl);
            }

            $url = str_replace(
                ['{status}', '{message}', '{email}'],
                ['error', urlencode('Lien de vérification invalide ou expiré.'), ''],
                $template
            );

            return redirect()->away($url);
        });
    })->create();
