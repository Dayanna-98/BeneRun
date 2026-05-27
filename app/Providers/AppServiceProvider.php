<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        RateLimiter::for('auth-login', function (Request $request) {
            $email = (string) $request->input('email', '');

            return [
                Limit::perMinute(8)->by($request->ip()),
                Limit::perMinute(5)->by($email !== '' ? strtolower($email) : $request->ip()),
            ];
        });

        RateLimiter::for('password-reset', function (Request $request) {
            $email = (string) $request->input('email', '');

            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perMinute(3)->by($email !== '' ? strtolower($email) : $request->ip()),
            ];
        });

        RateLimiter::for('email-verification', function (Request $request) {
            $email = (string) $request->input('email', '');

            return [
                Limit::perMinute(8)->by($request->ip()),
                Limit::perMinute(4)->by($email !== '' ? strtolower($email) : $request->ip()),
            ];
        });

        RateLimiter::for('maps-resolve', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        RateLimiter::for('emergency-actions', function (Request $request) {
            $actor = $request->user('sanctum');
            $key = $actor?->id_utilisateur !== null
                ? 'user:'.$actor->id_utilisateur
                : 'ip:'.$request->ip();

            return Limit::perMinute(20)->by($key);
        });

        RateLimiter::for('notifications-read', function (Request $request) {
            $actor = $request->user('sanctum');
            $key = $actor?->id_utilisateur !== null
                ? 'user:'.$actor->id_utilisateur
                : 'ip:'.$request->ip();

            return Limit::perMinute(60)->by($key);
        });
    }
}
