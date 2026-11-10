<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CapsuleLifecycleTest extends DuskTestCase
{
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
