<?php

namespace Tests\Unit;

use App\Capsules\TextSegmentation;
use PHPUnit\Framework\TestCase;

class TextSegmentationTest extends TestCase
{
    public function test_graphemes_preserve_combining_marks_emoji_and_line_endings(): void
    {
        $this->assertSame(
            ["е\u{0301}", ' ', '👩‍🔬', "\r\n", 'A'],
            TextSegmentation::graphemes("е\u{0301} 👩‍🔬\r\nA"),
        );
    }

    public function test_chunks_keep_links_words_and_spacing_without_changing_the_text(): void
    {
        $text = "Привет  https://example.test/слово\nCafe\u{0301} 👩‍🔬";
        $chunks = TextSegmentation::chunks($text);

        $this->assertSame($text, implode('', $chunks));
        $this->assertContains('https://example.test/слово', $chunks);
        $this->assertContains("Cafe\u{0301}", $chunks);
        $this->assertContains('👩‍🔬', $chunks);
    }
}
