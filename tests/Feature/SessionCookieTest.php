<?php

namespace Tests\Feature;

use Tests\TestCase;

class SessionCookieTest extends TestCase
{
    public function test_production_defaults_to_secure_lax_cookies_and_local_http_remains_supported(): void
    {
        $environment = $_SERVER['APP_ENV'] ?? null;

        try {
            $_SERVER['APP_ENV'] = 'production';
            $production = require config_path('session.php');
            $_SERVER['APP_ENV'] = 'local';
            $local = require config_path('session.php');
        } finally {
            if ($environment === null) {
                unset($_SERVER['APP_ENV']);
            } else {
                $_SERVER['APP_ENV'] = $environment;
            }
        }

        $this->assertTrue($production['secure']);
        $this->assertSame('lax', $production['same_site']);
        $this->assertFalse($local['secure']);
    }
}
