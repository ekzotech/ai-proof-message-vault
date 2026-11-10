<?php

namespace Tests\Feature;

use App\Capsules\CapsuleState;
use App\Capsules\TransformationPipeline;
use App\Capsules\TransformationPreset;
use App\Models\Capsule;
use App\Models\CapsulePublication;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class CapsulePublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_accessible_text_is_frozen_and_still_subject_to_the_opening_time(): void
    {
        $draft = Capsule::factory()->create(['body' => 'Исходное доступное послание']);
        $snapshot = $this->snapshot($draft);
        $snapshot->issueReaderToken();
        $snapshot->save();
        $url = route('capsules.read', ['token' => $snapshot->reader_token, 'view' => 'plain']);
        $draft->update(['body' => 'Изменённый источник']);

        $this->withoutVite()->get($url)->assertOk()->assertDontSee('Исходное доступное послание');
        $this->travelTo($snapshot->opens_at);
        $this->get($url)->assertOk()->assertSee('Исходное доступное послание')->assertDontSee('Изменённый источник');
        $this->assertStringNotContainsString('Исходное доступное послание', $snapshot->getRawOriginal('accessible_text'));
    }

    public function test_a_locked_capsule_does_not_expose_source_snapshot_or_token_in_the_response(): void
    {
        $draft = Capsule::factory()->create(['title' => 'Unique draft title', 'body' => 'Unique private original']);
        $snapshot = $this->snapshot($draft);
        $snapshot->issueReaderToken();
        $snapshot->save();
        $this->travelTo($snapshot->opens_at->subSecond());

        $response = $this->withoutVite()->get(route('capsules.read', $snapshot->reader_token))->assertOk();
        $payload = $response->getContent().json_encode($response->headers->all(), JSON_UNESCAPED_UNICODE);

        foreach ([$draft->title, $draft->body, $snapshot->title, $snapshot->segments[0]['text'],
            $snapshot->reader_token, $snapshot->reader_token_hash, $snapshot->seed, 'wire:snapshot', 'data-csrf'] as $secret) {
            $this->assertStringNotContainsString($secret, $payload);
        }
    }

    public function test_guest_owner_and_other_author_follow_the_capsule_route_matrix(): void
    {
        $draft = Capsule::factory()->create();
        $other = User::factory()->create();
        $edit = route('capsules.edit', $draft);
        $preview = route('capsules.preview', $draft);
        $details = route('capsules.publication', $draft);
        $this->withoutVite();

        foreach ([$edit, $preview, $details] as $private) {
            $this->get($private)->assertRedirect(route('login'));
        }

        $this->actingAs($draft->author)->get($edit)->assertOk();
        $this->get($preview)->assertOk();
        $this->get($details)->assertNotFound();
        $snapshot = $this->snapshot($draft);
        $snapshot->issueReaderToken();
        $snapshot->save();
        $public = route('capsules.read', $snapshot->reader_token);

        $this->get($edit)->assertForbidden();
        $this->get($preview)->assertForbidden();
        $this->get($details)->assertOk();
        $this->get($public)->assertSee('Капсула ожидает открытия');
        $this->actingAs($other)->get($details)->assertForbidden();
        $this->get($public)->assertSee('Капсула ожидает открытия');

        $this->travelTo($snapshot->opens_at);
        $this->get($public)->assertSee('Капсула открыта');
        $snapshot->revoked_at = now();
        $snapshot->save();
        $this->get($public)->assertNotFound();
        $this->actingAs($draft->author)->get($details)->assertSee('Ссылка отозвана');
    }

    public function test_the_reader_limit_recovers_after_the_window_expires(): void
    {
        $snapshot = $this->snapshot(Capsule::factory()->create());
        $snapshot->issueReaderToken();
        $snapshot->save();
        $url = route('capsules.read', $snapshot->reader_token);
        $first = $this->withoutVite()->get($url)->assertOk();
        $first->assertHeader('X-RateLimit-Limit', '120');

        RateLimiter::increment(md5('capsule-reader'.'127.0.0.1'), 60, 119);
        $this->get($url)->assertTooManyRequests();
        $this->travel(61)->seconds();
        $this->get($url)->assertOk();
    }

    public function test_deleting_a_capsule_removes_its_snapshot_and_reader_link(): void
    {
        $draft = Capsule::factory()->create();
        $snapshot = $this->snapshot($draft);
        $snapshot->issueReaderToken();
        $snapshot->save();
        $link = route('capsules.read', $snapshot->reader_token);
        $delete = route('capsules.destroy', $draft);

        $this->withoutVite()->actingAs(User::factory()->create())->delete($delete)->assertForbidden();
        $this->actingAs($draft->author)->get(route('capsules.delete', $draft))->assertSee('Удалить капсулу?');
        $this->delete($delete)->assertRedirect(route('dashboard'));
        $this->assertModelMissing($draft);
        $this->assertModelMissing($snapshot);
        $this->get($link)->assertNotFound();
    }

    public function test_revoking_a_link_hides_it_and_makes_it_unavailable(): void
    {
        $draft = Capsule::factory()->create();
        $snapshot = $this->snapshot($draft);
        $snapshot->issueReaderToken();
        $snapshot->save();
        $link = route('capsules.read', $snapshot->reader_token);
        $revoke = route('capsules.revoke', $draft);

        $this->withoutVite()->get($link)->assertOk();
        $this->actingAs(User::factory()->create())->patch($revoke)->assertForbidden();
        $this->actingAs($draft->author)->patch($revoke)->assertRedirect(route('capsules.publication', $draft));
        $this->get($link)->assertNotFound();
        $this->get(route('capsules.read', str_repeat('0', 48)))->assertNotFound();
        $this->get(route('capsules.publication', $draft))->assertSee('Ссылка отозвана')->assertDontSee($snapshot->reader_token);
    }

    public function test_only_the_author_can_retrieve_the_reader_link_from_the_dashboard(): void
    {
        $draft = Capsule::factory()->create();
        $snapshot = $this->snapshot($draft);
        $snapshot->issueReaderToken();
        $snapshot->save();
        $url = route('capsules.publication', $draft);

        $this->withoutVite()->actingAs(User::factory()->create())->get($url)->assertForbidden();
        $this->actingAs($draft->author)->get(route('dashboard'))->assertSee($url);
        $this->get($url)->assertOk()->assertSee('Копировать ссылку')->assertSee($snapshot->reader_token);
    }

    public function test_after_opening_the_reader_sees_the_saved_snapshot_without_rerunning_the_pipeline(): void
    {
        $draft = Capsule::factory()->create(['title' => 'Живой заголовок', 'body' => 'Живой текст']);
        $snapshot = $this->snapshot($draft);
        $snapshot->issueReaderToken();
        $snapshot->save();
        $this->mock(TransformationPipeline::class, fn ($mock) => $mock->shouldNotReceive('transform'));

        $this->travelTo($snapshot->opens_at);
        $this->withoutVite()->get(route('capsules.read', $snapshot->reader_token))
            ->assertOk()->assertHeader('Cache-Control', 'no-store, private')->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertSee('Сохранённый заголовок')->assertSee('Снимок')
            ->assertDontSee($draft->title)->assertDontSee($draft->body);
    }

    public function test_the_waiting_page_only_exposes_the_opening_time(): void
    {
        $draft = Capsule::factory()->create(['body' => 'Закрытый оригинал']);
        $snapshot = $this->snapshot($draft);
        $snapshot->issueReaderToken();
        $snapshot->save();

        $response = $this->withoutVite()->get(route('capsules.read', $snapshot->reader_token))
            ->assertOk()->assertSee('Капсула ожидает открытия')
            ->assertSee($snapshot->opens_at->toIso8601String())
            ->assertDontSee($snapshot->title)->assertDontSee('Снимок')
            ->assertDontSee($draft->body)->assertDontSee('wire:snapshot');

        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $response->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $response->assertHeader('Content-Security-Policy', "default-src 'none'; style-src 'self'; img-src 'self'; base-uri 'none'; form-action 'none'; frame-ancestors 'none'");
        config(['capsules.request_no_ai' => true]);
        $this->get(route('capsules.read', $snapshot->reader_token))
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive, noai, noimageai');
    }

    public function test_publication_requires_a_verified_author_and_an_attestation(): void
    {
        $author = User::factory()->unverified()->create();
        $draft = Capsule::factory()->for($author, 'author')->create(['opens_at' => now()->addHour()]);
        $data = ['version' => 0, 'preset' => 'gentle'];

        $this->withoutVite()->actingAs($author)->get(route('capsules.preview', $draft))
            ->assertSee('подтвердите почту')->assertDontSee('Запечатать и опубликовать');
        $this->post(route('capsules.publish', $draft), $data)->assertRedirect(route('verification.notice'));

        $author->markEmailAsVerified();
        $this->get(route('capsules.preview', ['capsule' => $draft, 'preset' => 'experimental']))
            ->assertSee('Экспериментальный')->assertSee('Запечатать и опубликовать');
        $this->post(route('capsules.publish', $draft), $data)->assertSessionHasErrors('author_attested');
        $this->assertDatabaseCount('capsule_publications', 0);
    }

    public function test_publishing_is_atomic_and_idempotent_and_locks_the_draft(): void
    {
        $draft = Capsule::factory()->create(['opens_at' => now()->addHour()]);
        $url = route('capsules.publish', $draft);
        $data = ['version' => 0, 'preset' => 'experimental', 'author_attested' => '1'];

        $this->actingAs(User::factory()->create())->post($url, $data)->assertForbidden();
        $this->actingAs($draft->author)->post($url, [...$data, 'version' => 5])->assertSessionHasErrors('version');
        $this->assertDatabaseCount('capsule_publications', 0);
        $this->post($url, $data)->assertRedirect(route('dashboard'));
        $first = $draft->publication()->sole();
        $this->post($url, $data)->assertRedirect(route('dashboard'));

        $this->assertDatabaseCount('capsule_publications', 1);
        $this->assertSame($first->reader_token, $draft->publication()->sole()->reader_token);
        $this->assertSame(1, $draft->fresh()->lock_version);
        $this->withoutVite()->get(route('capsules.edit', $draft))->assertForbidden();
    }

    public function test_failed_publication_does_not_change_the_draft_version(): void
    {
        $draft = Capsule::factory()->create(['opens_at' => now()->subMinute()]);
        $this->actingAs($draft->author)->post(route('capsules.publish', $draft), [
            'version' => 0, 'preset' => 'gentle', 'author_attested' => '1',
        ])->assertSessionHasErrors('opens_at');

        $this->assertSame(0, $draft->fresh()->lock_version);
        $this->assertDatabaseCount('capsule_publications', 0);
    }

    public function test_availability_changes_at_the_utc_boundary_and_revocation_takes_priority(): void
    {
        $snapshot = $this->snapshot(Capsule::factory()->create());
        $opening = $snapshot->opens_at;

        $this->assertSame(CapsuleState::Draft, CapsuleState::at(null, $opening));
        $this->assertSame(CapsuleState::Waiting, CapsuleState::at($snapshot, $opening->subSecond()));
        $this->assertSame(CapsuleState::Open, CapsuleState::at($snapshot, $opening));

        $snapshot->revoked_at = $opening->subMinute();
        $this->assertSame(CapsuleState::Revoked, CapsuleState::at($snapshot, $opening->subSecond()));
        $this->assertSame(CapsuleState::Revoked, CapsuleState::at($snapshot, $opening));
    }

    public function test_reader_tokens_are_opaque_encrypted_and_unique(): void
    {
        $first = $this->snapshot(Capsule::factory()->create());
        $first->issueReaderToken();
        $first->save();
        $second = $this->snapshot(Capsule::factory()->create());
        $second->issueReaderToken();

        $this->assertMatchesRegularExpression('/\A[a-f0-9]{48}\z/', $first->reader_token);
        $this->assertNotSame($first->reader_token, $second->reader_token);
        $this->assertSame($first->reader_token, $first->fresh()->reader_token);
        $this->assertNotSame($first->reader_token, $first->getRawOriginal('reader_token'));
        $this->assertSame(hash('sha256', $first->reader_token), $first->reader_token_hash);

        $this->expectException(UniqueConstraintViolationException::class);
        $second->reader_token_hash = $first->reader_token_hash;
        $second->save();
    }

    public function test_the_snapshot_is_stored_separately_and_encrypted(): void
    {
        $draft = Capsule::factory()->create();
        $snapshot = $this->snapshot($draft);
        $stored = DB::table('capsule_publications')->where('id', $snapshot->id)->value('segments');

        $this->assertStringNotContainsString('Снимок', $stored);
        $this->assertTrue($snapshot->capsule->is($draft));
        $this->assertSame([['kind' => 'text', 'text' => 'Снимок']], $draft->publication()->sole()->segments);

        $draft->update(['title' => 'Изменённый черновик']);
        $this->assertSame('Сохранённый заголовок', $snapshot->fresh()->title);
    }

    private function snapshot(Capsule $draft): CapsulePublication
    {
        return $draft->publication()->create([
            'title' => 'Сохранённый заголовок',
            'opens_at' => now()->addHour(),
            'algorithm_version' => 1,
            'seed' => $draft->transformation_seed,
            'preset' => TransformationPreset::Gentle,
            'segments' => [['kind' => 'text', 'text' => 'Снимок']],
            'accessible_text' => $draft->body,
        ]);
    }
}
