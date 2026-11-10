<?php

namespace Tests\Browser;

use Facebook\WebDriver\Chrome\ChromeDevToolsDriver;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ClipboardTest extends DuskTestCase
{
    public function test_the_author_can_copy_the_link_and_use_the_manual_fallback(): void
    {
        $snapshot = $this->publishFixture('Текст проверки', [['kind' => 'text', 'text' => 'Снимок проверки']]);

        $this->browse(function (Browser $browser) use ($snapshot): void {
            $browser->loginAs($snapshot->capsule->author)
                ->visitRoute('capsules.publication', $snapshot->capsule)
                ->waitFor('#reader-link');
            $this->allowClipboard($browser);
            $browser->press('Копировать ссылку')->waitForText('Ссылка скопирована.');
            $this->assertSame(route('capsules.read', $snapshot->reader_token), $this->readClipboard($browser));

            $browser->script("Object.defineProperty(navigator, 'clipboard', { value: undefined, configurable: true });");
            $browser->press('Копировать ссылку')->waitForText('Скопируйте выделенную ссылку вручную.');
            $this->assertTrue($browser->script(<<<'JS'
                const link = document.querySelector('#reader-link');
                return link.selectionStart === 0 && link.selectionEnd === link.value.length;
                JS)[0]);
        });
    }

    public function test_mouse_selection_and_ctrl_c_copy_the_dom_text_to_the_real_clipboard(): void
    {
        $original = 'alpha beta';
        $transformed = "\u{0430}l\u{200B}pha beta";
        $snapshot = $this->publishFixture($original, [['kind' => 'text', 'text' => $transformed]]);

        $this->browse(function (Browser $browser) use ($snapshot, $original, $transformed): void {
            $browser->visitRoute('capsules.read', $snapshot->reader_token)->waitFor('.capsule-text');
            $cdp = $this->allowClipboard($browser);

            $this->dragAndCopy($browser, $cdp, $transformed);
            $copied = $this->readClipboard($browser);
            $this->assertSame($transformed, $copied);
            $this->assertNotSame($original, $copied);

            $browser->clickLink('Обычный текст для скринридера')->waitForText('Обработанный текст');
            $this->dragAndCopy($browser, $cdp, $original);
            $this->assertSame($original, $this->readClipboard($browser));
        });
    }

    private function allowClipboard(Browser $browser): ChromeDevToolsDriver
    {
        $cdp = new ChromeDevToolsDriver($browser->driver);
        $cdp->execute('Page.bringToFront');
        $cdp->execute('Browser.grantPermissions', [
            'origin' => config('app.url'),
            'permissions' => ['clipboardReadWrite', 'clipboardSanitizedWrite'],
        ]);

        return $cdp;
    }

    private function readClipboard(Browser $browser): string
    {
        $value = $browser->driver->executeAsyncScript(<<<'JS'
            const done = arguments[arguments.length - 1];
            navigator.clipboard.readText().then(done, error => done({ error: error.name }));
            JS);
        $this->assertIsString($value);

        return $value;
    }

    private function dragAndCopy(Browser $browser, ChromeDevToolsDriver $cdp, string $expected): void
    {
        $position = $browser->script(<<<'JS'
            const range = document.createRange();
            range.selectNodeContents(document.querySelector('.capsule-text'));
            const rect = range.getBoundingClientRect();
            return { start: rect.left - 1, end: rect.right + 2, y: rect.top + rect.height / 2 };
            JS)[0];
        $cdp->execute('Input.dispatchMouseEvent', [
            'type' => 'mousePressed', 'x' => $position['start'], 'y' => $position['y'],
            'button' => 'left', 'buttons' => 1, 'clickCount' => 1,
        ]);
        $cdp->execute('Input.dispatchMouseEvent', [
            'type' => 'mouseMoved', 'x' => $position['end'], 'y' => $position['y'], 'button' => 'left', 'buttons' => 1,
        ]);
        $cdp->execute('Input.dispatchMouseEvent', [
            'type' => 'mouseReleased', 'x' => $position['end'], 'y' => $position['y'],
            'button' => 'left', 'buttons' => 0, 'clickCount' => 1,
        ]);
        $this->assertSame($expected, $browser->script('return window.getSelection().toString();')[0]);

        $key = ['modifiers' => 2, 'key' => 'c', 'code' => 'KeyC', 'windowsVirtualKeyCode' => 67];
        $cdp->execute('Input.dispatchKeyEvent', ['type' => 'rawKeyDown', ...$key]);
        $cdp->execute('Input.dispatchKeyEvent', ['type' => 'keyUp', ...$key]);
    }
}
