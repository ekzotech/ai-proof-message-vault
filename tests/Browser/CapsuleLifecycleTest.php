<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CapsuleLifecycleTest extends DuskTestCase
{
    public function test_a_phone_author_can_create_publish_and_read_without_horizontal_scrolling(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $author, Browser $guest) use ($user): void {
            $cdp = $this->phoneViewport($author);
            $this->phoneViewport($guest);
            $author->loginAs($user)->visit('/capsules/create')->waitFor('#capsule-title');
            $key = ['key' => 'Tab', 'code' => 'Tab', 'windowsVirtualKeyCode' => 9];
            $cdp->execute('Input.dispatchKeyEvent', ['type' => 'rawKeyDown', ...$key]);
            $cdp->execute('Input.dispatchKeyEvent', ['type' => 'keyUp', ...$key]);
            $this->assertSame('skip-link', $author->script('return document.activeElement.className;')[0]);
            $this->assertFitsPhone($author);

            $author->type('#capsule-title', 'Письмо на телефон')
                ->type('#capsule-body', str_repeat('Длинноесловобезразрыва', 15))
                ->press('Сохранить черновик')->waitForText('Редактировать послание');
            $draft = $user->capsules()->sole();
            $draft->update(['opens_at' => now()->addHour()]);
            $author->visitRoute('capsules.preview', $draft)
                ->check('author_attested')->press('Запечатать и опубликовать')->waitForLocation('/dashboard');

            $publication = $draft->publication()->sole();
            $publication->forceFill(['opens_at' => now()->subSecond()])->save();
            $guest->visitRoute('capsules.read', $publication->reader_token)->assertSee($draft->title);
            $this->assertFitsPhone($guest);
        });
    }

    private function assertFitsPhone(Browser $browser): void
    {
        $viewport = $browser->script('return { width: innerWidth, content: document.documentElement.scrollWidth };')[0];
        $this->assertSame(390, $viewport['width']);
        $this->assertLessThanOrEqual(390, $viewport['content']);
    }

    public function test_a_previous_guest_loses_access_after_revocation_and_deletion(): void
    {
        $revoked = $this->publishFixture('Первая запись', [['kind' => 'text', 'text' => 'Первый снимок']], 'Капсула для отзыва');
        $deleted = $this->publishFixture('Вторая запись', [['kind' => 'text', 'text' => 'Второй снимок']], 'Капсула для удаления');

        $this->browse(function (Browser $author, Browser $guest) use ($revoked, $deleted): void {
            $firstLink = route('capsules.read', $revoked->reader_token);
            $guest->visit($firstLink)->assertSee('Первый снимок');
            $author->loginAs($revoked->capsule->author)->visitRoute('capsules.publication', $revoked->capsule)
                ->press('Отозвать ссылку')->waitFor('dialog[open]')
                ->press('Подтвердить отзыв')->waitUntilMissing('#reader-link');
            $guest->refresh()->assertSee('Капсула недоступна')->assertDontSee('Первый снимок');

            $secondLink = route('capsules.read', $deleted->reader_token);
            $guest->visit($secondLink)->assertSee('Второй снимок');
            $author->loginAs($deleted->capsule->author)->visitRoute('capsules.publication', $deleted->capsule)
                ->clickLink('Удалить капсулу')->waitForText('Удалить капсулу?')
                ->press('Удалить капсулу')->waitForText('Капсула удалена.');
            $guest->refresh()->assertSee('Капсула недоступна')->assertDontSee('Второй снимок');
            $this->assertModelMissing($deleted);
        });
    }
}
