<?php

namespace App\Http\Controllers;

use App\Capsules\CapsuleState;
use App\Capsules\PublishCapsule;
use App\Capsules\TransformationPreset;
use App\Models\Capsule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class CapsulePublicationController
{
    public function revoke(Capsule $capsule): RedirectResponse
    {
        $publication = $capsule->publication()->firstOrFail();
        $capsule->publication()->whereKey($publication->id)->whereNull('revoked_at')->update(['revoked_at' => now()]);

        return redirect()->route('capsules.publication', $capsule);
    }

    public function show(Capsule $capsule): Response
    {
        $publication = $capsule->publication()->firstOrFail();
        $state = CapsuleState::at($publication, now());
        $readerUrl = $state === CapsuleState::Revoked ? null : rtrim(config('app.url'), '/')
            .route('capsules.read', ['token' => $publication->reader_token], absolute: false);

        return response()->view('capsules.publication', compact('capsule', 'publication', 'state', 'readerUrl'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function store(Request $request, Capsule $capsule, PublishCapsule $publisher): RedirectResponse
    {
        $data = $request->validate([
            'version' => ['required', 'integer', 'min:0'],
            'preset' => ['required', Rule::enum(TransformationPreset::class)],
            'author_attested' => ['accepted'],
        ]);

        $publisher->handle($request->user(), $capsule, (int) $data['version'], TransformationPreset::from($data['preset']));

        return redirect()->route('dashboard')->with('status', 'Капсула опубликована.');
    }
}
