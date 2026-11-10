<?php

namespace App\Capsules;

use Random\Randomizer;

class TransformationPipeline
{
    public const VERSION = 1;

    public function transform(string $text, ?Randomizer $random = null): TransformationResult
    {
        $random ??= new Randomizer;
        $chunks = TextSegmentation::chunks($text);
        $segments = [];
        $plain = '';
        $count = count($chunks);

        for ($index = 0; $index < $count; $index++) {
            $word = $chunks[$index];
            $group = $this->groupAt($chunks, $index);

            if ($group !== null && $random->getInt(1, 3) === 1) {
                if ($plain !== '') {
                    $segments[] = ['kind' => 'text', 'text' => $plain];
                    $plain = '';
                }
                $segments[] = ['kind' => 'rtl', 'text' => $group];
                $index += 2;

                continue;
            }

            if (preg_match('/\A[\p{Latin}\p{Cyrillic}][\p{Latin}\p{Cyrillic}\p{M}]*\z/u', $word) === 1) {
                $word = ZeroWidthSpaces::insert(Homoglyphs::replace($word, $random), $random);
            }
            $plain .= $word;
        }

        if ($plain !== '') {
            $segments[] = ['kind' => 'text', 'text' => $plain];
        }

        return new TransformationResult(self::VERSION, $segments);
    }

    /** @param list<string> $chunks */
    private function groupAt(array $chunks, int $index): ?string
    {
        if (($chunks[$index + 1] ?? null) !== ' ' || ! isset($chunks[$index + 2])) {
            return null;
        }

        return BidiWordGroup::reverse($chunks[$index].' '.$chunks[$index + 2]);
    }
}
