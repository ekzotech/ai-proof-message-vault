<?php

namespace Tests\Feature;

use App\Capsules\TransformationPreset;
use App\Models\Capsule;
use App\Models\CapsulePublication;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CapsulePublicationTest extends TestCase
{
    use RefreshDatabase;

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
