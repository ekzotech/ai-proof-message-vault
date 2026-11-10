<?php

namespace Tests\Feature;

use App\Capsules\CapsuleState;
use App\Capsules\TransformationPreset;
use App\Models\Capsule;
use App\Models\CapsulePublication;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CapsulePublicationTest extends TestCase
{
    use RefreshDatabase;

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
        ]);
    }
}
