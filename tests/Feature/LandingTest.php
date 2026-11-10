<?php

namespace Tests\Feature;

use App\Livewire\CapsuleExample;
use Tests\TestCase;

class LandingTest extends TestCase
{
    public function test_the_landing_page_explains_the_vault_and_shows_an_example(): void
    {
        $this->withoutVite()
            ->get('/')
            ->assertOk()
            ->assertSee('Капсула времени для текста')
            ->assertSee('Создать капсулу')->assertSee(route('register'))
            ->assertSee('Восстановление текста остаётся возможным.')
            ->assertSeeLivewire(CapsuleExample::class);
    }
}
