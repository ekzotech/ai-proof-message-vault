<?php

namespace App\Http\Controllers;

use App\Models\Capsule;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CapsuleDeletionController
{
    public function confirm(Capsule $capsule): View
    {
        return view('capsules.delete', compact('capsule'));
    }

    public function destroy(Capsule $capsule): RedirectResponse
    {
        $capsule->delete();

        return redirect()->route('dashboard')->with('status', 'Капсула удалена.');
    }
}
