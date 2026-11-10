<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Rules\NewPassword;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegisterController
{
    public function __invoke(Request $request): RedirectResponse
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => Str::lower($request->input('email'))]);
        }

        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:254', 'unique:users,email'],
            'password' => ['bail', 'required', 'string', 'confirmed', 'min:12', new NewPassword],
        ]);

        $attributes['password'] = Hash::make($attributes['password']);

        try {
            $user = User::create($attributes);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['email' => __('validation.unique', ['attribute' => 'email'])]);
        }

        Auth::login($user);
        event(new Registered($user));

        return redirect()->route('verification.notice');
    }
}
