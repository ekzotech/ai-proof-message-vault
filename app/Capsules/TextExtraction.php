<?php

namespace App\Capsules;

use Dom\HTMLDocument;
use Normalizer;

class TextExtraction
{
    public static function foldLookalikes(string $text): string
    {
        return strtr($text, array_flip(Homoglyphs::PAIRS));
    }

    public static function cleanSpacing(string $text): string
    {
        $text = preg_replace('/[\x{00AD}\x{200B}\x{2060}\x{FEFF}]/u', '', $text);

        return preg_replace('/[\p{Zs}\t]+/u', ' ', $text);
    }

    public static function normalize(string $text, bool $compatibility = false): string
    {
        return Normalizer::normalize($text, $compatibility ? Normalizer::FORM_KC : Normalizer::FORM_C);
    }

    public static function readDom(string $markup, bool $restoreBidi = false): string
    {
        $document = HTMLDocument::createFromString($markup, LIBXML_NOERROR, 'UTF-8');
        $capsule = $document->querySelector('.capsule-text');

        if ($restoreBidi && $capsule !== null) {
            foreach ($capsule->querySelectorAll('bdo[dir="rtl"]') as $group) {
                $group->textContent = implode('', array_reverse(TextSegmentation::graphemes($group->textContent)));
            }
        }

        return $capsule?->textContent ?? '';
    }
}
