<?php

namespace Tests\Feature;

use App\Capsules\TransformationPreset;
use App\Models\Capsule;
use App\Models\CapsulePublication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CapsulePublicationTest extends TestCase
{
    use RefreshDatabase;

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
