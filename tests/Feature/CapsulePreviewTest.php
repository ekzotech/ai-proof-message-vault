<?php

namespace Tests\Feature;

use App\Models\Capsule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CapsulePreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_saved_draft_preview_is_private_and_contains_transformed_text(): void
    {
        $draft = Capsule::factory()->create(['body' => str_repeat('Послание для будущего. ', 8)]);
        $url = route('capsules.preview', $draft);

        $this->withoutVite()->get($url)->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
        $response = $this->actingAs($draft->author)->get($url)->assertOk()
            ->assertSee('Последняя сохранённая версия')
            ->assertSee('capsule-text')
            ->assertSee('Все методы вместе')
            ->assertSee('Совпало')
            ->assertSee('Различается')
            ->assertDontSee($draft->body);

        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));

        $this->get($url.'?preset=experimental')->assertOk()->assertSee('Экспериментальный');
        $this->get($url.'?preset[]=experimental')->assertNotFound();
    }
}
