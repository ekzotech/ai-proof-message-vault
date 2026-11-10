<?php

namespace Tests\Feature;

use App\Livewire\CapsuleExample;
use Livewire\Livewire;
use Tests\TestCase;

class CapsuleExampleTest extends TestCase
{
    public function test_example_switches_between_sealed_and_open_states(): void
    {
        Livewire::test(CapsuleExample::class)
            ->assertSee('Запечатана')
            ->assertDontSee('яблоню')
            ->call('toggle')
            ->assertSee('Открыта')
            ->assertSee('яблоню')
            ->call('toggle')
            ->assertSee('Запечатана')
            ->assertDontSee('яблоню');
    }
}
