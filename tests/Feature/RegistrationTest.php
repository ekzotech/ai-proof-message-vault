<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_author_can_register_and_is_signed_in(): void
    {
        Notification::fake();
        $this->withoutVite()->get(route('register'))->assertOk();

        $this->post(route('register'), [
            'name' => 'Мария',
            'email' => 'Author@example.test',
            'password' => 'letters-for-tomorrow',
            'password_confirmation' => 'letters-for-tomorrow',
        ])->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'author@example.test')->sole();

        $this->assertAuthenticatedAs($user);
        Notification::assertSentTo($user, VerifyEmail::class);
        $this->assertTrue(Hash::check('letters-for-tomorrow', $user->password));
    }

    public function test_registration_rejects_a_duplicate_address_and_invalid_password(): void
    {
        User::factory()->create(['email' => 'author@example.test']);

        $this->post(route('register'), [
            'name' => 'Мария',
            'email' => 'AUTHOR@example.test',
            'password' => str_repeat('я', 37),
            'password_confirmation' => str_repeat('я', 37),
        ])->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }
}
