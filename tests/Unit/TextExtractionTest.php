<?php

namespace Tests\Unit;

use App\Capsules\TextExtraction;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\CapsuleSamples;

class TextExtractionTest extends TestCase
{
    #[DataProviderExternal(CapsuleSamples::class, 'domCases')]
    public function test_dom_reads_text_in_document_order(string $original, string $markup, string $expected): void
    {
        $extracted = TextExtraction::readDom($markup);
        $this->assertSame($expected, $extracted);
        $this->assertSame($original === $expected, $original === $extracted);
    }
}
