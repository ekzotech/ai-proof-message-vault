<?php

namespace App\Capsules;

use Random\Randomizer;

class Homoglyphs
{
    public const PAIRS = [
        'A' => 'А', 'B' => 'В', 'C' => 'С', 'E' => 'Е', 'H' => 'Н', 'K' => 'К',
        'M' => 'М', 'O' => 'О', 'P' => 'Р', 'T' => 'Т', 'X' => 'Х', 'Y' => 'У',
        'a' => 'а', 'c' => 'с', 'e' => 'е', 'o' => 'о', 'p' => 'р', 'x' => 'х', 'y' => 'у',
    ];

    public static function replace(string $word, Randomizer $random): string
    {
        $table = self::PAIRS + array_flip(self::PAIRS);
        $graphemes = TextSegmentation::graphemes($word);

        foreach ($graphemes as &$grapheme) {
            if (isset($table[$grapheme]) && $random->getInt(1, 4) === 1) {
                $grapheme = $table[$grapheme];
            }
        }

        return implode('', $graphemes);
    }
}
