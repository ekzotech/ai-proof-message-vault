<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Rules\NewPassword;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordRecoveryController
{
    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'string', 'email', 'max:254']]);

        $email = Str::lower($data['email']);
        Cache::lock($this->lockKey($email), 30)->get(
            fn () => Password::sendResetLink(['email' => $email]),
        );

        return back()->with('status', 'Если этот адрес зарегистрирован, на него придёт письмо со ссылкой.');
    }

    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:254'],
            'token' => ['required', 'string', 'max:255'],
            'password' => ['bail', 'required', 'string', 'confirmed', 'min:12', new NewPassword],
        ]);

        $data['email'] = Str::lower($data['email']);
        $resetUser = Cache::lock($this->lockKey($data['email']), 30)->get(function () use ($data) {
            return DB::transaction(function () use ($data): ?User {
                $resetUser = null;
                Password::reset($data, function (User $user, string $password) use (&$resetUser): void {
                    $user->forceFill(['password' => Hash::make($password)])->setRememberToken(Str::random(60));
                    $user->save();
                    $resetUser = $user;
                });

                return $resetUser;
            });
        });

        if (! $resetUser instanceof User) {
            throw ValidationException::withMessages(['email' => 'Ссылка недействительна. Запросите новую ссылку для восстановления.']);
        }

        event(new PasswordReset($resetUser));

        return redirect()->route('login')->with('status', 'Пароль обновлён. Войдите с новым паролем.');
    }

    private function lockKey(string $email): string
    {
        return 'auth:password-recovery:'.hash('sha256', $email);
    }
}
