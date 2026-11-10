<?php

namespace App\Http\Controllers;

use App\Capsules\TransformationPipeline;
use App\Models\Capsule;
use Illuminate\Http\Response;

class CapsulePreviewController
{
    public function __invoke(Capsule $capsule, TransformationPipeline $pipeline): Response
    {
        $preview = $pipeline->transform($capsule->body, $capsule->transformation_seed);

        return response()->view('capsules.preview', compact('capsule', 'preview'))
            ->header('Cache-Control', 'private, no-store');
    }
}
