<?php

namespace Tests\Feature;

use App\Livewire\CapsuleDraftEditor;
use App\Models\Capsule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CapsuleDraftTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_editor_creates_a_draft_owned_by_the_signed_in_author(): void
    {
        $author = User::factory()->create();
        $this->withoutVite()->get(route('capsules.create'))->assertRedirect(route('login'));
        $this->actingAs($author)->get(route('capsules.create'))->assertSeeLivewire(CapsuleDraftEditor::class);

        $editor = Livewire::test(CapsuleDraftEditor::class)
            ->set('title', 'Письмо из прошлого')
            ->set('body', 'Заметка для будущего')
            ->call('save')->assertHasNoErrors();

        $draft = $author->capsules()->sole();
        $editor->assertRedirect(route('capsules.edit', $draft));
        $this->assertSame('Заметка для будущего', $draft->body);
    }

    public function test_the_draft_route_requires_the_owner(): void
    {
        $draft = Capsule::factory()->create(['body' => 'Приватное послание']);
        $url = route('capsules.edit', $draft);

        $this->withoutVite()->get($url)->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get($url)->assertForbidden()->assertDontSee($draft->body);
        $this->actingAs($draft->author)->get($url)->assertOk()->assertSee($draft->body);
    }

    public function test_drafts_preserve_unicode_and_layout_but_reject_unsafe_or_oversized_text(): void
    {
        $author = User::factory()->create();
        $editor = Livewire::actingAs($author)->test(CapsuleDraftEditor::class)->set('title', 'Текст');

        foreach (["\u{00A0}\u{2003}", "скрыто\u{202E}направление", str_repeat('я', 12001)] as $invalid) {
            $editor->set('body', $invalid)->call('save')->assertHasErrors('body');
        }

        $this->assertDatabaseCount('capsules', 0);
        $body = "  Первая строка\nCafe\u{0301} и 👩‍🔬\n";
        $editor->set('body', $body)->call('save')->assertHasNoErrors();
        $this->assertSame($body, $author->capsules()->sole()->body);
    }
}
