<?php

namespace App\Http\Controllers;

use App\Capsules\CapsuleState;
use App\Models\CapsulePublication;
use Illuminate\Http\Response;

class CapsuleReaderController
{
    public function __invoke(string $token): Response
    {
        $publication = CapsulePublication::where('reader_token_hash', CapsulePublication::hashReaderToken($token))->first();
        $state = CapsuleState::at($publication, now());
        abort_if(in_array($state, [CapsuleState::Draft, CapsuleState::Revoked], true), 404);

        return $state === CapsuleState::Open
            ? response()->view('capsules.read', ['publication' => $publication])
            : response()->view('capsules.waiting', ['opensAt' => $publication->opens_at]);

    }
}
