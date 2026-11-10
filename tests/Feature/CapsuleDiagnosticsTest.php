<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class CapsuleDiagnosticsTest extends TestCase
{
    public function test_unexpected_capsule_errors_log_only_the_route_and_error_type(): void
    {
        Log::spy();
        config(['app.debug' => false]);
        Route::get('/capsules/diagnostics-probe/{token}', function (): never {
            throw new RuntimeException('private body and bearer token');
        })->name('test.capsule-probe');

        $this->get('/capsules/diagnostics-probe/synthetic-token')->assertServerError()
            ->assertDontSee('private body and bearer token');
        Log::shouldHaveReceived('error')->once()->with('Capsule request failed.', [
            'route' => 'test.capsule-probe',
            'exception' => RuntimeException::class,
        ]);
    }
}
