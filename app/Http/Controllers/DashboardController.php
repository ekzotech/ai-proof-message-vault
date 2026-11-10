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

        $published = $request->user()->capsules()
            ->whereHas('publication')
            ->with('publication:id,capsule_id,opens_at,revoked_at')
            ->latest('updated_at')->orderByDesc('id')
            ->paginate(12, ['id', 'title'], pageName: 'published');

        return view('dashboard', compact('drafts', 'published'));
    }
}
