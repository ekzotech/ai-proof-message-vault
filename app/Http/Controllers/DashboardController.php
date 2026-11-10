<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController
{
    public function __invoke(Request $request): View
    {
        $drafts = $request->user()->capsules()
            ->whereDoesntHave('publication')
            ->latest('updated_at')
            ->orderByDesc('id')
            ->paginate(12, ['id', 'title', 'opens_at', 'updated_at']);

        return view('dashboard', compact('drafts'));
    }
}
