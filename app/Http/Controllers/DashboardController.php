<?php

namespace App\Http\Controllers;

use App\Capsules\CapsuleState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController
{
    public function __invoke(Request $request): View
    {
        $requested = $request->query('status');
        $state = is_string($requested) ? CapsuleState::tryFrom($requested) : null;
        abort_if($requested !== null && $state === null, 404);
        $now = now();
        $capsules = $request->user()->capsules()->latest('updated_at')->orderByDesc('id');

        $drafts = null;
        if ($state === null || $state === CapsuleState::Draft) {
            $drafts = (clone $capsules)->whereDoesntHave('publication')
                ->paginate(12, ['id', 'title', 'opens_at', 'updated_at'])->withQueryString();
        }

        $published = null;
        if ($state !== CapsuleState::Draft) {
            $published = $capsules->whereHas('publication', fn ($query) => match ($state) {
                CapsuleState::Waiting => $query->whereNull('revoked_at')->where('opens_at', '>', $now),
                CapsuleState::Open => $query->whereNull('revoked_at')->where('opens_at', '<=', $now),
                CapsuleState::Revoked => $query->whereNotNull('revoked_at'),
                default => $query,
            })->with('publication:id,capsule_id,opens_at,revoked_at')
                ->paginate(12, ['id', 'title'], pageName: 'published')->withQueryString();
        }

        return view('dashboard', compact('drafts', 'published', 'state', 'now'));
    }
}
