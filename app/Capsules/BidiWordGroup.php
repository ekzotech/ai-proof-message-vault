<?php

namespace App\Capsules;

class BidiWordGroup
{
    public static function reverse(string $text): ?string
    {
        if (preg_match('/\A[\p{Latin}\p{Cyrillic}]{2,8}(?: [\p{Latin}\p{Cyrillic}]{2,8}){1,2}\z/u', $text) !== 1) {
            return null;
        }

        return implode('', array_reverse(TextSegmentation::graphemes($text)));
    }
}
