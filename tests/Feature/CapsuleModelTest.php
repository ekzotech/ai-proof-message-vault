<?php

namespace Tests\Feature;

use App\Models\Capsule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CapsuleModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_drafts_have_distinct_persistent_transformation_seeds(): void
    {
        $draft = Capsule::factory()->create();
        $other = Capsule::factory()->create();

        $this->assertSame(32, strlen($draft->transformation_seed));
        $this->assertSame($draft->transformation_seed, $draft->fresh()->transformation_seed);
        $this->assertNotSame($draft->transformation_seed, $other->transformation_seed);
    }

    public function test_the_database_stores_ciphertext_and_the_model_reads_the_original(): void
    {
        $message = "Капсула для будущего.\nЗаписка остаётся читаемой.";
        $draft = Capsule::factory()->create(['body' => $message]);

        $stored = DB::table('capsules')->where('id', $draft->id)->value('body');

        $this->assertIsString($stored);
        $this->assertStringNotContainsString($message, $stored);
        $this->assertSame($message, $draft->fresh()->body);
        $this->assertArrayNotHasKey('body', $draft->fresh()->toArray());
    }

    public function test_drafts_belong_to_an_author_and_are_removed_with_their_account(): void
    {
        $author = User::factory()->create();
        $draft = Capsule::factory()->for($author, 'author')->create();
        $otherDraft = Capsule::factory()->create();

        $this->assertTrue($draft->author->is($author));
        $this->assertTrue($author->capsules()->sole()->is($draft));

        $author->delete();

        $this->assertModelMissing($draft);
        $this->assertModelExists($otherDraft);
    }
}
