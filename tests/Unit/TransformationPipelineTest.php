<?php

namespace Tests\Unit;

use App\Capsules\TransformationPipeline;
use App\Capsules\TransformationPreset;
use LengthException;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\CapsuleSamples;

class TransformationPipelineTest extends TestCase
{
    #[DataProviderExternal(CapsuleSamples::class, 'originals')]
    public function test_the_pipeline_handles_the_extraction_corpus(string $original): void
    {
        $result = (new TransformationPipeline)->transform($original, 'corpus-seed');
        $extracted = implode('', array_column($result->segments, 'text'));

        $this->assertTrue(mb_check_encoding($extracted, 'UTF-8'));
        $this->assertGreaterThanOrEqual(mb_strlen($original), mb_strlen($extracted));
    }

    public function test_the_pipeline_returns_versioned_segments_and_preserves_urls_and_emoji(): void
    {
        $text = str_repeat('письмо миру hello world ', 10)."https://example.test/слово 👩‍🔬\n";
        $pipeline = new TransformationPipeline;
        $result = $pipeline->transform($text, 'fixed-seed', TransformationPreset::Experimental);

        $this->assertSame(TransformationPipeline::VERSION, $result->version);
        $this->assertContains('rtl', array_column($result->segments, 'kind'));
        $this->assertStringEndsWith("https://example.test/слово 👩‍🔬\n", implode('', array_column($result->segments, 'text')));
        $this->assertNotSame($text, implode('', array_column($result->segments, 'text')));
        $this->assertSame($result->segments, $pipeline->transform($text, 'fixed-seed', TransformationPreset::Experimental)->segments);
        $this->assertNotSame($result->segments, $pipeline->transform($text, 'other-seed')->segments);
        $this->assertNotContains('rtl', array_column($pipeline->transform($text, 'fixed-seed')->segments, 'kind'));
    }

    public function test_maximum_length_output_stays_bounded_and_unaffected_text_is_kept(): void
    {
        $pipeline = new TransformationPipeline;
        $text = str_repeat('мир и текст ', 1000);
        $result = $pipeline->transform($text, 'fixed-seed', TransformationPreset::Experimental);
        $output = implode('', array_column($result->segments, 'text'));

        $this->assertLessThanOrEqual(513, count($result->segments));
        $this->assertLessThanOrEqual(15000, mb_strlen($output));
        $this->assertSame([['kind' => 'text', 'text' => '👩‍🔬!']], $pipeline->transform('👩‍🔬!', 'fixed-seed')->segments);
        $this->assertSame([], $pipeline->transform('', 'fixed-seed')->segments);

        $this->expectException(LengthException::class);
        $pipeline->transform($text.'а', 'fixed-seed');
    }
}
