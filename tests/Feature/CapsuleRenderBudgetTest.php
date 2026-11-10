<?php

namespace Tests\Feature;

use App\Capsules\TransformationPipeline;
use App\Capsules\TransformationPreset;
use App\Rules\CapsuleText;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class CapsuleRenderBudgetTest extends TestCase
{
    public function test_the_maximum_message_fits_the_rendering_budget(): void
    {
        $unit = "Заметка для будущего hello world & <фрагмент> 👩‍🔬 https://example.test/текст\n";
        $text = mb_substr(str_repeat($unit, 400), 0, CapsuleText::MAX_BODY);
        $started = hrtime(true);

        $result = (new TransformationPipeline)->transform($text, 'render-budget', TransformationPreset::Experimental);
        $markup = Blade::render('<x-capsules.segments :segments="$segments" />', ['segments' => $result->segments]);
        $elapsed = (hrtime(true) - $started) / 1_000_000;

        $this->assertSame(12000, mb_strlen($text));
        $this->assertLessThan(100_000, strlen($markup));
        $this->assertLessThan(2000, $elapsed);

        if (getenv('VAULT_BENCHMARK') === 'true') {
            printf("Maximum capsule: %.1f ms, %d HTML bytes.\n", $elapsed, strlen($markup));
        }
    }
}
