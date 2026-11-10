<?php

namespace Tests\Feature;

use App\Livewire\CapsuleDraftEditor;
use App\Models\Capsule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class CapsuleDraftTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_new_draft_is_blocked_when_the_author_exhausts_the_creation_budget(): void
    {
        $author = User::factory()->create();
        RateLimiter::increment('capsules:create:'.$author->id, 60, 12);
        $editor = Livewire::actingAs($author)->test(CapsuleDraftEditor::class)
            ->set('title', 'Заголовок')->set('body', 'Текст послания');

        $editor->call('save')->assertHasErrors('creationLimit');
        $this->assertDatabaseCount('capsules', 0);
        $this->travel(61)->seconds();
        $editor->call('save')->assertHasNoErrors();
        $this->assertDatabaseCount('capsules', 1);
    }

    public function test_the_editor_creates_a_draft_owned_by_the_signed_in_author(): void
    {
        $author = User::factory()->create();
        $this->withoutVite()->get(route('capsules.create'))->assertRedirect(route('login'));
        $this->actingAs($author)->get(route('capsules.create'))
            ->assertSeeLivewire(CapsuleDraftEditor::class)
            ->assertSee('Заголовок капсулы')
            ->assertSee('из 12000')
            ->assertSee('из 120');

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
        $this->actingAs($draft->author)->get($url)->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')->assertSee($draft->body);
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

    public function test_only_the_owner_can_update_a_draft_through_the_component(): void
    {
        $draft = Capsule::factory()->create(['body' => 'Исходный текст']);
        $other = User::factory()->create();
        Livewire::actingAs($other)->test(CapsuleDraftEditor::class, ['capsule' => $draft])->assertForbidden();

        $editor = Livewire::actingAs($draft->author)->test(CapsuleDraftEditor::class, ['capsule' => $draft])
            ->assertSet('body', 'Исходный текст')
            ->set('title', 'Новый заголовок')
            ->set('body', ' ')
            ->call('save')->assertHasErrors('body')
            ->assertSet('title', 'Новый заголовок');

        $editor->set('body', 'Новый текст')->call('save')->assertHasNoErrors()->assertRedirect(route('capsules.edit', $draft));
        $this->assertSame('Новый текст', $draft->fresh()->body);
    }

    public function test_a_stale_editor_does_not_overwrite_a_newer_version(): void
    {
        $draft = Capsule::factory()->create();
        Livewire::actingAs($draft->author);
        $first = Livewire::test(CapsuleDraftEditor::class, ['capsule' => $draft]);
        $second = Livewire::test(CapsuleDraftEditor::class, ['capsule' => $draft]);

        $first->set('body', 'Первая сохранённая версия')->call('save')->assertHasNoErrors();
        $second->set('body', 'Текст из старой вкладки')->call('save')->assertHasErrors('conflict')
            ->assertSee('Открыть свежую версию в новой вкладке')
            ->assertSet('body', 'Текст из старой вкладки');

        $this->assertSame('Первая сохранённая версия', $draft->fresh()->body);
        $this->assertSame(1, $draft->fresh()->lock_version);
    }

    public function test_opening_uses_the_authors_timezone_and_requires_an_offset_for_ambiguous_time(): void
    {
        $author = User::factory()->create(['timezone' => 'America/New_York']);
        $editor = Livewire::actingAs($author)->test(CapsuleDraftEditor::class)
            ->set('title', 'Послание')->set('body', 'Текст послания')
            ->set('opening', '2026-03-08T02:30')
            ->call('save')->assertHasErrors('opening')
            ->set('opening', '2026-11-01T01:30')
            ->call('save')->assertHasErrors('openingOffset');

        $editor->set('openingOffset', '-05:00')->call('save')->assertHasNoErrors();

        $draft = $author->capsules()->sole();
        $this->assertSame('2026-11-01 06:30:00', $draft->opens_at->toDateTimeString());
        Livewire::test(CapsuleDraftEditor::class, ['capsule' => $draft])
            ->assertSet('opening', '2026-11-01T01:30')
            ->assertSet('openingOffset', '-05:00');
    }
}
