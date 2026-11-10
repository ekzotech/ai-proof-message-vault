<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ReaderPresentationTest extends DuskTestCase
{
    public function test_reversed_groups_read_in_the_same_visual_direction_as_plain_text(): void
    {
        $phrase = 'alpha beta';
        $paragraph = "\n".str_repeat('Длинная строка ', 18);
        $snapshot = $this->publishFixture($phrase.$paragraph, [
            ['kind' => 'rtl', 'text' => strrev($phrase)],
            ['kind' => 'text', 'text' => $paragraph],
        ]);

        $this->browse(function (Browser $browser) use ($snapshot): void {
            $this->phoneViewport($browser);
            $browser->visitRoute('capsules.read', $snapshot->reader_token)
                ->waitFor('.capsule-text bdo')->screenshot('reader-transformed');
            $transformed = $browser->script($this->glyphPositions(true))[0];
            $browser->clickLink('Обычный текст для скринридера')
                ->waitForText('Обработанный текст')->screenshot('reader-plain');
            $plain = $browser->script($this->glyphPositions(false))[0];

            $this->assertGreaterThan($transformed['last'], $transformed['first']);
            $this->assertGreaterThan($plain['first'], $plain['last']);
            $this->assertEqualsWithDelta($plain['first'], $transformed['last'], 1.0);
            $this->assertEqualsWithDelta($plain['last'], $transformed['first'], 1.0);
            $this->assertTrue($plain['wraps']);
            $this->assertTrue($transformed['wraps']);
        });
    }

    private function glyphPositions(bool $reversed): string
    {
        $selector = $reversed ? '.capsule-text bdo' : '.capsule-text';

        return <<<JS
            const node = document.querySelector('$selector').firstChild;
            const range = document.createRange();
            const leftAt = index => {
                range.setStart(node, index);
                range.setEnd(node, index + 1);
                return range.getBoundingClientRect().left;
            };
            const first = leftAt(0);
            const last = leftAt(9);
            const block = document.querySelector('.capsule-text');
            return { first, last, wraps: block.offsetHeight > 3 * parseFloat(getComputedStyle(block).lineHeight) };
            JS;
    }
}
