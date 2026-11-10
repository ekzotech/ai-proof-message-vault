<?php

namespace App\Capsules;

use Dom\HTMLDocument;
use Normalizer;

class TextExtraction
{
    public static function normalize(string $text, bool $compatibility = false): string
    {
        return Normalizer::normalize($text, $compatibility ? Normalizer::FORM_KC : Normalizer::FORM_C);
    }

    public static function readDom(string $markup): string
    {
        $document = HTMLDocument::createFromString($markup, LIBXML_NOERROR, 'UTF-8');

        return $document->querySelector('.capsule-text')?->textContent ?? '';
    }
}
