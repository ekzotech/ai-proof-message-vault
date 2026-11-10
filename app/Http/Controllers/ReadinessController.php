<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReadinessController
{
    public function __invoke(): JsonResponse
    {
        try {
            DB::table('capsule_publications')->limit(1)->exists();
        } catch (Throwable) {
            return response()->json(['status' => 'unavailable'], 503, ['Cache-Control' => 'no-store']);
        }

        return response()->json(['status' => 'ok'], 200, ['Cache-Control' => 'no-store']);
    }
}
