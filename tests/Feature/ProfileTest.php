<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_author_can_update_only_their_own_timezone(): void
    {
        $author = User::factory()->create();
        $other = User::factory()->create();
        $this->assertSame('UTC', $author->fresh()->timezone);

        $this->patch(route('profile.update'), ['timezone' => 'Asia/Yekaterinburg'])->assertRedirect(route('login'));
        $this->actingAs($author)->patch(route('profile.update'), ['timezone' => '+05:00'])->assertSessionHasErrors('timezone');
        $this->patch(route('profile.update'), ['timezone' => 'Asia/Yekaterinburg', 'id' => $other->id])
            ->assertRedirect(route('profile.edit'));

        $this->assertSame('Asia/Yekaterinburg', $author->fresh()->timezone);
        $this->assertSame('UTC', $other->fresh()->timezone);
    }
}
