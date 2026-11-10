<?php

namespace App\Http\Controllers;

use App\Capsules\PublishCapsule;
use App\Capsules\TransformationPreset;
use App\Models\Capsule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CapsulePublicationController
{
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
