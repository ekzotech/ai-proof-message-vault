<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class SessionController
{
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:254'],
            'password' => ['required', 'string', 'max:72'],
        ]);

        $credentials['email'] = Str::lower($credentials['email']);
        $attemptKey = 'auth:login:'.hash('sha256', $credentials['email'].'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($attemptKey, 5)) {
            throw new TooManyRequestsHttpException(
                RateLimiter::availableIn($attemptKey),
                'Слишком много попыток входа.',
            );
        }

        if (! Auth::attempt($credentials)) {
            RateLimiter::hit($attemptKey, 60);

            throw ValidationException::withMessages(['email' => 'Неверный адрес или пароль.']);
        }

        RateLimiter::clear($attemptKey);

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
