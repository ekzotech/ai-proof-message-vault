<?php

namespace Tests\Feature;

use App\Models\Capsule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CapsuleModelTest extends TestCase
{
    use RefreshDatabase;

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
