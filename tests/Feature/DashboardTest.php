<?php

namespace Tests\Feature;

use App\Models\Capsule;
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

    public function test_dashboard_paginates_only_the_authors_drafts_in_stable_order(): void
    {
        $author = User::factory()->create();
        $drafts = Capsule::factory()->count(13)->for($author, 'author')
            ->create(['updated_at' => '2026-11-10 00:00:00']);
        $foreign = Capsule::factory()->create(['title' => 'Чужой черновик']);
        $first = $drafts->first();
        $last = $drafts->last();

        $this->withoutVite()->actingAs($author)->get(route('dashboard'))
            ->assertSee($last->title)->assertDontSee($first->title)->assertDontSee($foreign->title);

        $this->get(route('dashboard', ['page' => 2]))
            ->assertSee($first->title)->assertDontSee($last->title)->assertDontSee($foreign->title);
    }
}
