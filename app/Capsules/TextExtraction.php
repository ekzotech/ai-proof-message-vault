<?php

namespace App\Capsules;

use Dom\HTMLDocument;

class TextExtraction
{
    public static function readDom(string $markup): string
    {
        $document = HTMLDocument::createFromString($markup, LIBXML_NOERROR, 'UTF-8');

        return $document->querySelector('.capsule-text')?->textContent ?? '';
    }
}
