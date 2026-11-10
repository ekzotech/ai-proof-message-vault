<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordRecoveryController
{
    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'string', 'email', 'max:254']]);

        Password::sendResetLink(['email' => Str::lower($data['email'])]);

        return back()->with('status', 'Если этот адрес зарегистрирован, на него придёт письмо со ссылкой.');
    }
}
