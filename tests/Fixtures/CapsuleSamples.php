<?php

namespace Tests\Fixtures;

class CapsuleSamples
{
    public static function originals(): array
    {
        return array_map(fn (array $case): array => [$case[0]], self::extractionCases());
    }

    public static function domCases(): array
    {
        return array_map(fn (array $case): array => array_slice($case, 0, 3), self::extractionCases());
    }

    public static function extractionCases(): array
    {
        return [
            'Russian lookalike' => ['море', '<div class="capsule-text">мoре</div>', 'мoре', true],
            'English zero-width' => ['hello', "<div class=\"capsule-text\">hel\u{200B}lo</div>", "hel\u{200B}lo", true],
            'mixed and URL' => [
                'Привет world https://example.test/текст 👩‍🔬',
                '<div class="capsule-text">Привет wоrld https://example.test/текст 👩‍🔬</div>',
                'Привет wоrld https://example.test/текст 👩‍🔬',
                true,
            ],
            'bidi override' => ['hello world', '<div class="capsule-text"><bdo dir="rtl">dlrow olleh</bdo></div>', 'dlrow olleh', true],
            'HTML entities' => ['A < B & C', '<div class="capsule-text">A &lt; B &amp; C</div>', 'A < B & C', true],
            'missing text' => ['утраченная строка', '<div class="capsule-text">иная строка</div>', 'иная строка', false],
        ];
    }
}
