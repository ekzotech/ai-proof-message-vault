<?php

use App\Http\Middleware\EnsureDuskIsolation;

return [
    'isolated' => env('VAULT_DUSK_ISOLATED', false),
    'middleware' => ['web', EnsureDuskIsolation::class],
];
