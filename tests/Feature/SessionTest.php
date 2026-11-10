<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_author_can_sign_in_and_out_with_rotated_session_credentials(): void
    {
        $user = User::factory()->create(['email' => 'author@example.test', 'password' => 'letters-for-tomorrow']);
        $this->withoutVite()->get(route('login'))->assertOk();
        $anonymousSession = session()->getId();

        $this->post(route('login'), [
            'email' => 'AUTHOR@example.test',
            'password' => 'letters-for-tomorrow',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($anonymousSession, session()->getId());
        $signedInToken = session()->token();

        $this->post(route('logout'))->assertRedirect(route('home'));

        $this->assertGuest();
        $this->assertNotSame($signedInToken, session()->token());
        $this->post(route('logout'))->assertRedirect(route('login'));
    }

    public function test_invalid_credentials_keep_the_author_signed_out(): void
    {
        User::factory()->create(['email' => 'author@example.test', 'password' => 'letters-for-tomorrow']);

        foreach (['author@example.test', 'unknown@example.test'] as $email) {
            $this->post(route('login'), [
                'email' => $email,
                'password' => 'wrong-password',
            ])->assertSessionHasErrors(['email' => 'Неверный адрес или пароль.']);
        }

        $this->assertGuest();
    }

    public function test_five_failed_attempts_temporarily_block_sign_in(): void
    {
        User::factory()->create(['email' => 'author@example.test', 'password' => 'letters-for-tomorrow']);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login'), [
                'email' => 'author@example.test',
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('email');
        }

        $credentials = ['email' => 'AUTHOR@example.test', 'password' => 'letters-for-tomorrow'];

        $this->post(route('login'), $credentials)->assertTooManyRequests()->assertHeader('Retry-After');
        $this->assertGuest();

        $this->travel(61)->seconds();
        $this->post(route('login'), $credentials)->assertRedirect(route('home'));
        $this->assertAuthenticated();
    }
}
