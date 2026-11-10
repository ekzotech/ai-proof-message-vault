<?php

namespace App\Http\Controllers;

use App\Capsules\ExtractionReport;
use App\Capsules\TransformationPipeline;
use App\Capsules\TransformationPreset;
use App\Models\Capsule;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Blade;

class CapsulePreviewController
{
    public function __invoke(Request $request, Capsule $capsule, TransformationPipeline $pipeline): Response
    {
        $requested = $request->query('preset', TransformationPreset::Gentle->value);
        $preset = is_string($requested) ? TransformationPreset::tryFrom($requested) : null;
        abort_if($preset === null, 404);

        $preview = $pipeline->transform($capsule->body, $capsule->transformation_seed, $preset);
        $markup = Blade::render('<x-capsules.segments :segments="$segments" />', ['segments' => $preview->segments]);
        $extraction = ExtractionReport::measure($capsule->body, $markup);

        return response()->view('capsules.preview', compact('capsule', 'preview', 'preset', 'extraction'))
            ->header('Cache-Control', 'private, no-store');
    }
}
