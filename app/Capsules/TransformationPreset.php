<?php

namespace App\Capsules;

enum TransformationPreset: string
{
    case Gentle = 'gentle';
    case Experimental = 'experimental';

    public function label(): string
    {
        return match ($this) {
            self::Gentle => 'Щадящий',
            self::Experimental => 'Экспериментальный',
        };
    }
}
