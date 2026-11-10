<?php

namespace Tests\Unit;

use App\Capsules\ExtractionReport;
use App\Capsules\TextExtraction;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\CapsuleSamples;

class TextExtractionTest extends TestCase
{
    #[DataProviderExternal(CapsuleSamples::class, 'extractionCases')]
    public function test_combined_extraction_reports_successes_and_failures(string $original, string $markup, string $dom, bool $combined): void
    {
        $report = ExtractionReport::measure($original, $markup);
        $this->assertSame(array_keys(ExtractionReport::LABELS), array_keys($report));
        $this->assertSame($original === $dom, $report['dom']);
        $this->assertSame($combined, $report['combined']);
    }

    public function test_bidi_aware_extraction_reverses_only_marked_groups(): void
    {
        $markup = '<div class="capsule-text">A <bdo dir="rtl">dlrow olleh</bdo> B</div>';
        $this->assertSame('A dlrow olleh B', TextExtraction::readDom($markup));
        $this->assertSame('A hello world B', TextExtraction::readDom($markup, restoreBidi: true));
    }

    public function test_folding_recovers_equivalent_spelling(): void
    {
        $this->assertSame('Coco', TextExtraction::foldLookalikes('Сосо'));
        $this->assertSame(TextExtraction::foldLookalikes('море'), TextExtraction::foldLookalikes('мoре'));
    }

    public function test_cleanup_removes_invisible_spaces_and_preserves_emoji(): void
    {
        $text = "hel\u{200B}lo\u{2060}\t\u{00A0}world\n👩‍🔬";
        $this->assertSame("hello world\n👩‍🔬", TextExtraction::cleanSpacing($text));
    }

    public function test_canonical_and_compatibility_normalization_differ(): void
    {
        $text = "Cafe\u{0301} Ａ";
        $this->assertSame('Café Ａ', TextExtraction::normalize($text));
        $this->assertSame('Café A', TextExtraction::normalize($text, compatibility: true));
        $this->assertNotSame('море', TextExtraction::normalize('мoре', compatibility: true));
    }

    #[DataProviderExternal(CapsuleSamples::class, 'domCases')]
    public function test_dom_reads_text_in_document_order(string $original, string $markup, string $expected): void
    {
        $extracted = TextExtraction::readDom($markup);
        $this->assertSame($expected, $extracted);
        $this->assertSame($original === $expected, $original === $extracted);
    }
}
