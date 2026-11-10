<?php

namespace Tests\Feature;

use App\Models\Capsule;
use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CapsuleKeyRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_previous_key_recovers_the_draft_ciphertext_after_rotation(): void
    {
        $draft = Capsule::factory()->create(['body' => 'Записка из тестовой резервной копии.']);
        $previous = app('encrypter')->getKey();
        $rotated = new Encrypter(random_bytes(32), 'AES-256-CBC');
        $rotated->previousKeys([$previous]);

        $this->assertSame($draft->body, $rotated->decrypt($draft->getRawOriginal('body'), unserialize: false));
    }
}
