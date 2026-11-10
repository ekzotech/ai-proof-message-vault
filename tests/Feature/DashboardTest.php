<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_dashboard_is_private_and_prompts_unverified_authors(): void
    {
        $this->withoutVite()->get(route('dashboard'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->unverified()->create())
            ->get(route('dashboard'))->assertOk()->assertSee('Подтвердить почту');

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))->assertOk()->assertDontSee('Подтвердить почту');
    }
}
