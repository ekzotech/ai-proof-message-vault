<?php

namespace App\Capsules;

class ExtractionReport
{
    public const LABELS = [
        'dom' => 'Текст из HTML',
        'nfc' => 'Нормализация NFC',
        'nfkc' => 'Нормализация NFKC',
        'spacing' => 'Очистка пробелов',
        'lookalikes' => 'Сведение похожих букв',
        'bidi' => 'Чтение с учётом направления',
        'combined' => 'Все методы вместе',
    ];

    /** @return array<string, bool> */
    public static function measure(string $original, string $markup): array
    {
        $dom = TextExtraction::readDom($markup);
        $bidi = TextExtraction::readDom($markup, restoreBidi: true);

        return [
            'dom' => $original === $dom,
            'nfc' => TextExtraction::normalize($original) === TextExtraction::normalize($dom),
            'nfkc' => TextExtraction::normalize($original, compatibility: true) === TextExtraction::normalize($dom, compatibility: true),
            'spacing' => TextExtraction::cleanSpacing($original) === TextExtraction::cleanSpacing($dom),
            'lookalikes' => TextExtraction::foldLookalikes($original) === TextExtraction::foldLookalikes($dom),
            'bidi' => $original === $bidi,
            'combined' => self::combined($original) === self::combined($bidi),
        ];
    }

    private static function combined(string $text): string
    {
        return TextExtraction::foldLookalikes(TextExtraction::cleanSpacing(TextExtraction::normalize($text, compatibility: true)));
    }
}
