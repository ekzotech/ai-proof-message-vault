<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_ready_returns_only_the_status_when_the_database_is_available(): void
    {
        $this->get('/ready')->assertOk()->assertExactJson(['status' => 'ok']);
    }

    public function test_ready_returns_503_without_details_even_when_sessions_use_the_database(): void
    {
        config([
            'session.driver' => 'database',
            'database.default' => 'readiness_outage',
            'database.connections.readiness_outage' => [
                'driver' => 'sqlite',
                'database' => sys_get_temp_dir().DIRECTORY_SEPARATOR.'missing-vault-'.bin2hex(random_bytes(8)).'.sqlite',
            ],
        ]);

        try {
            $this->get('/ready')->assertServiceUnavailable()
                ->assertExactJson(['status' => 'unavailable'])->assertDontSee('missing-vault');
        } finally {
            config(['database.default' => 'sqlite', 'session.driver' => 'array']);
        }
    }
}
