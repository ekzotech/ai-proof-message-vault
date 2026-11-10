<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
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
}
