<?php

namespace Tests\Unit;

use App\Capsules\Homoglyphs;
use App\Capsules\ZeroWidthSpaces;
use PHPUnit\Framework\TestCase;
use Random\Engine\Mt19937;
use Random\Randomizer;

class TextTransformationsTest extends TestCase
{
    public function test_only_allowed_graphemes_are_replaced_in_both_directions(): void
    {
        $random = new Randomizer(new Mt19937(4));
        $this->assertStringContainsString('а', Homoglyphs::replace(str_repeat('a', 40), $random));
        $this->assertStringContainsString('a', Homoglyphs::replace(str_repeat('а', 40), $random));

        $unsupported = "Ж9👩‍🔬e\u{0301}";
        $this->assertSame($unsupported, Homoglyphs::replace($unsupported, $random));
    }

    public function test_the_invisible_space_splits_only_eligible_words_between_graphemes(): void
    {
        $random = new Randomizer(new Mt19937(4));
        $word = "Cafe\u{0301}";
        $result = ZeroWidthSpaces::insert($word, $random);

        $this->assertSame($word, str_replace(ZeroWidthSpaces::SPACE, '', $result));
        $this->assertSame(1, substr_count($result, ZeroWidthSpaces::SPACE));
        $this->assertStringEndsWith("e\u{0301}", $result);
        $this->assertSame('https://example.test/слово', ZeroWidthSpaces::insert('https://example.test/слово', $random));
        $this->assertSame("👩‍🔬\n", ZeroWidthSpaces::insert("👩‍🔬\n", $random));
    }
}
