<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_requesting_recovery_gives_the_same_response_for_any_address(): void
    {
        Notification::fake();
        $author = User::factory()->create(['email' => 'author@example.test']);
        $status = 'Если этот адрес зарегистрирован, на него придёт письмо со ссылкой.';

        foreach (['AUTHOR@example.test', 'unknown@example.test'] as $email) {
            $this->from(route('password.request'))->post(route('password.email'), ['email' => $email])
                ->assertRedirect(route('password.request'))
                ->assertSessionHas('status', $status);
        }

        Notification::assertSentToTimes($author, ResetPassword::class, 1);
        Notification::assertCount(1);
        $notice = Notification::sent($author, ResetPassword::class)->sole();
        $link = $notice->toMail($author)->actionUrl;
        $this->assertSame(
            rtrim(config('app.url'), '/').route('password.reset', ['token' => $notice->token, 'email' => $author->email], absolute: false),
            $link,
        );
        $this->withoutVite()->get($link)->assertOk();
    }

    public function test_a_reset_token_changes_the_password_only_once(): void
    {
        $author = User::factory()->create();
        $data = [
            'email' => $author->email,
            'token' => Password::createToken($author),
            'password' => 'new-password-for-tomorrow',
            'password_confirmation' => 'new-password-for-tomorrow',
        ];

        $this->post(route('password.update'), $data)->assertRedirect(route('login'));
        $this->assertTrue(Hash::check($data['password'], $author->fresh()->password));
        $this->post(route('password.update'), $data)->assertSessionHasErrors('email');
    }

    public function test_an_expired_token_cannot_change_the_password(): void
    {
        $author = User::factory()->create();
        $token = Password::createToken($author);
        $this->travel(config('auth.passwords.users.expire') + 1)->minutes();

        $this->post(route('password.update'), [
            'email' => $author->email,
            'token' => $token,
            'password' => 'new-password-for-tomorrow',
            'password_confirmation' => 'new-password-for-tomorrow',
        ])->assertSessionHasErrors('email');

        $this->assertSame($author->password, $author->fresh()->password);
    }
}
