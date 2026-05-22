<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = $request->input('email') ?? 'unknown';
            $ip = $request->ip() ?? '0.0.0.0';
            return Limit::perMinute(5, 15)->by($email . '|' . $ip);
        });

        RateLimiter::for('registration', function (Request $request) {
            $ip = $request->ip() ?? '0.0.0.0';
            return Limit::perMinute(3, 60)->by($ip);
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
