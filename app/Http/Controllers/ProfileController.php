<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate(['timezone' => ['required', 'string', 'timezone:all']]);

        $request->user()->forceFill($data)->save();

        return redirect()->route('profile.edit')->with('status', 'Часовой пояс сохранён.');
    }
}
