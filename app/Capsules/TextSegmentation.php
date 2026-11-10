<?php

namespace App\Capsules;

use InvalidArgumentException;

class TextSegmentation
{
    /** @return list<string> */
    public static function graphemes(string $text): array
    {
        $clusters = grapheme_str_split($text);

        if ($clusters === false) {
            throw new InvalidArgumentException('The message must be valid UTF-8.');
        }

        return $clusters;
    }

    /** @return list<string> */
    public static function chunks(string $text): array
    {
        $pattern = '~(?:https?://|www\.)[^\s]+|[\p{L}\p{M}\p{N}]+|\X~iu';

        if (preg_match_all($pattern, $text, $matches) === false) {
            throw new InvalidArgumentException('The message must be valid UTF-8.');
        }

        return $matches[0];
    }
}
