<?php

namespace Tests\Browser;

use App\Models\Capsule;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AuthorFlowTest extends DuskTestCase
{
    public function test_a_separate_reader_session_waits_until_the_scheduled_opening(): void
    {
        $draft = Capsule::factory()->create([
            'title' => 'Письмо с расписанием',
            'body' => 'Отложенное браузерное послание.',
            'opens_at' => now()->addHour(),
        ]);

        $this->browse(function (Browser $author, Browser $guest) use ($draft): void {
            $author->loginAs($draft->author)->visitRoute('capsules.preview', $draft)
                ->check('author_attested')->press('Запечатать и опубликовать')
                ->waitForLocation('/dashboard');

            $publication = $draft->publication()->sole();
            $link = route('capsules.read', $publication->reader_token);
            $guest->visit($link)->waitForText('Капсула ожидает открытия')
                ->assertDontSee($draft->title)->assertDontSee($draft->body);

            $publication->forceFill(['opens_at' => now()->subSecond()])->save();
            $guest->refresh()->waitForText($draft->title)->assertPresent('.capsule-text');
        });
    }

    public function test_an_author_corrects_an_error_edits_a_draft_and_previews_it(): void
    {
        $author = User::factory()->create();

        $this->browse(function (Browser $browser) use ($author): void {
            $browser->loginAs($author)->visit('/capsules/create')
                ->type('#capsule-title', 'Письмо из браузера')
                ->type('#capsule-body', ' ')
                ->press('Сохранить черновик')
                ->waitFor('#capsule-body-error')
                ->assertInputValue('#capsule-title', 'Письмо из браузера')
                ->type('#capsule-body', 'Первая версия послания.')
                ->press('Сохранить черновик')
                ->waitForText('Редактировать послание')
                ->type('#capsule-body', 'Текст для будущего читателя.')
                ->press('Сохранить черновик')
                ->waitForText('Черновик сохранён.')
                ->clickLink('Предпросмотр сохранённого текста')
                ->waitFor('.capsule-text')
                ->assertSee('Письмо из браузера')
                ->assertDontSeeIn('.capsule-text', 'Текст для будущего читателя.');

            $this->assertSame('Текст для будущего читателя.', $author->capsules()->sole()->body);
        });
    }

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
