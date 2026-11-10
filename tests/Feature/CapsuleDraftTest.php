<?php

namespace Tests\Feature;

use App\Models\Capsule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CapsuleDraftTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_draft_route_requires_the_owner(): void
    {
        $draft = Capsule::factory()->create(['body' => 'Приватное послание']);
        $url = route('capsules.edit', $draft);

        $this->withoutVite()->get($url)->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get($url)->assertForbidden()->assertDontSee($draft->body);
        $this->actingAs($draft->author)->get($url)->assertOk()->assertSee($draft->body);
    }
}
