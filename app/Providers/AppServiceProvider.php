<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
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
        RateLimiter::for('capsule-reader', fn (Request $request) => Limit::perMinute(120)->by($request->ip()));

        ResetPassword::createUrlUsing(
            fn (User $user, string $token): string => rtrim(config('app.url'), '/')
                .route('password.reset', ['token' => $token, 'email' => $user->email], absolute: false),
        );
        VerifyEmail::createUrlUsing(
            fn (User $user): string => rtrim(config('app.url'), '/').URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(config('auth.verification.expire', 60)),
                ['id' => $user->getKey(), 'hash' => sha1($user->getEmailForVerification())],
                absolute: false,
            ),
        );
    }
}
