<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class VaultSmokeTest extends DuskTestCase
{
    public function test_the_vault_home_page_loads_in_chrome(): void
    {
        $this->browse(fn (Browser $browser) => $browser->visit('/')
            ->assertSee('Капсула времени для текста')
            ->assertSeeLink('Создать капсулу'));
    }
}
