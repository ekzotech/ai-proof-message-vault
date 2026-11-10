<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class CapsuleMarkupTest extends TestCase
{
    public function test_renderer_escapes_each_segment_without_altering_spacing(): void
    {
        $markup = Blade::render('<x-capsules.segments :segments="$segments" />', [
            'segments' => [
                ['kind' => 'text', 'text' => 'A <script> & '],
                ['kind' => 'rtl', 'text' => 'dlrow olleh'],
                ['kind' => 'text', 'text' => "\nB"],
            ],
        ]);

        $this->assertStringContainsString("A &lt;script&gt; &amp; <bdo dir=\"rtl\">dlrow olleh</bdo>\nB", $markup);
        $this->assertStringNotContainsString('<script>', $markup);
    }
}
