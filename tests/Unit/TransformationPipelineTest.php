<?php

namespace Tests\Unit;

use App\Capsules\TransformationPipeline;
use PHPUnit\Framework\TestCase;
use Random\Engine\Mt19937;
use Random\Randomizer;

class TransformationPipelineTest extends TestCase
{
    public function test_the_pipeline_returns_versioned_segments_and_preserves_urls_and_emoji(): void
    {
        $text = str_repeat('письмо миру hello world ', 10)."https://example.test/слово 👩‍🔬\n";
        $result = (new TransformationPipeline)->transform($text, new Randomizer(new Mt19937(4)));

        $this->assertSame(TransformationPipeline::VERSION, $result->version);
        $this->assertContains('rtl', array_column($result->segments, 'kind'));
        $this->assertStringEndsWith("https://example.test/слово 👩‍🔬\n", implode('', array_column($result->segments, 'text')));
        $this->assertNotSame($text, implode('', array_column($result->segments, 'text')));
    }
}
