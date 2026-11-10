<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_endpoint_is_available_with_an_empty_test_database(): void
    {
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->assertFalse(DB::connection()->getSchemaBuilder()->hasTable('migrations'));

        $this->get('/up')->assertOk();
    }
}
