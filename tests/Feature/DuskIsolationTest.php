<?php

namespace Tests\Feature;

use Tests\TestCase;

class DuskIsolationTest extends TestCase
{
    public function test_browser_login_helpers_are_unavailable_without_the_isolated_runner(): void
    {
        $this->get('/_dusk/login/1')->assertNotFound();
    }
}
