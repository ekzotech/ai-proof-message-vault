<?php

namespace App\Capsules;

use App\Rules\CapsuleText;
use LengthException;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;

class TransformationPipeline
{
    public const VERSION = 1;

    private const MAX_BIDI_GROUPS = 256;

    public function transform(string $text, string $seed): TransformationResult
    {
        if (strlen($text) > 4 * CapsuleText::MAX_BODY || mb_strlen($text) > CapsuleText::MAX_BODY) {
            throw new LengthException('Capsule text exceeds the transformation limit.');
        }

        $random = new Randomizer(new Xoshiro256StarStar(hash('sha256', $seed, binary: true)));
        $chunks = TextSegmentation::chunks($text);
        $segments = [];
        $plain = '';
        $count = count($chunks);
        $bidiGroups = 0;

        for ($index = 0; $index < $count; $index++) {
            $word = $chunks[$index];
            $group = $bidiGroups < self::MAX_BIDI_GROUPS ? $this->groupAt($chunks, $index) : null;

            if ($group !== null && $random->getInt(1, 3) === 1) {
                if ($plain !== '') {
                    $segments[] = ['kind' => 'text', 'text' => $plain];
                    $plain = '';
                }
                $segments[] = ['kind' => 'rtl', 'text' => $group];
                $bidiGroups++;
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
