<?php

namespace Tests\Unit;

use App\Capsules\Homoglyphs;
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
}
