<?php

namespace App\Capsules;

final readonly class TransformationResult
{
    /** @param list<array{kind: 'text'|'rtl', text: string}> $segments */
    public function __construct(
        public int $version,
        public array $segments,
    ) {}
}
