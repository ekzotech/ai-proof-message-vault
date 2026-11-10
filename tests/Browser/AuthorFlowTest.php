<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AuthorFlowTest extends DuskTestCase
{
    public function test_an_author_registers_confirms_email_and_opens_the_dashboard(): void
    {
        $email = 'dusk-registration@example.test';

        $this->browse(function (Browser $browser) use ($email): void {
            $browser->visit('/register')
                ->type('name', 'Тестовый автор')
                ->type('email', $email)
                ->type('password', 'letters-for-tomorrow')
                ->type('password_confirmation', 'letters-for-tomorrow')
                ->press('Зарегистрироваться')
                ->waitForLocation('/verify-email')
                ->assertSee('Подтвердите почту');

            $author = User::whereEmail($email)->sole();
            $this->assertFalse($author->hasVerifiedEmail());

            $browser->visit((new VerifyEmail)->toMail($author)->actionUrl)
                ->waitForLocation('/dashboard')
                ->assertSeeLink('Создать черновик');

            $this->assertTrue($author->fresh()->hasVerifiedEmail());
        });
    }
}
