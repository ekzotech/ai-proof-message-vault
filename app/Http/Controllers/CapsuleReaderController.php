<?php

namespace App\Http\Controllers;

use App\Capsules\CapsuleState;
use App\Models\CapsulePublication;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CapsuleReaderController
{
    public function __invoke(Request $request, string $token): Response
    {
        $publication = CapsulePublication::where('reader_token_hash', CapsulePublication::hashReaderToken($token))->first();
        $state = CapsuleState::at($publication, now());
        abort_if(in_array($state, [CapsuleState::Draft, CapsuleState::Revoked], true), 404);

        if ($state === CapsuleState::Waiting) {
            return response()->view('capsules.waiting', ['opensAt' => $publication->opens_at]);
        }

        $plain = $request->query('view') === 'plain';
        $parameters = $plain ? ['token' => $token] : ['token' => $token, 'view' => 'plain'];
        $viewUrl = route('capsules.read', $parameters, absolute: false);

        return response()->view('capsules.read', compact('publication', 'plain', 'viewUrl'));

    }
}
