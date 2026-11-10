<?php

namespace App\Capsules;

use Random\Randomizer;

class ZeroWidthSpaces
{
    public const SPACE = "\u{200B}";

    public static function insert(string $word, Randomizer $random): string
    {
        if (preg_match('/\A[\p{Latin}\p{Cyrillic}][\p{Latin}\p{Cyrillic}\p{M}]*\z/u', $word) !== 1) {
            return $word;
        }

        $graphemes = TextSegmentation::graphemes($word);
        if (count($graphemes) < 3) {
            return $word;
        }

        $position = $random->getInt(1, count($graphemes) - 1);
        array_splice($graphemes, $position, 0, [self::SPACE]);

        return implode('', $graphemes);
    }
}
