<?php

namespace App\Capsules;

use App\Models\CapsulePublication;
use DateTimeInterface;

enum CapsuleState: string
{
    case Draft = 'draft';
    case Waiting = 'waiting';
    case Open = 'open';
    case Revoked = 'revoked';

    public static function at(?CapsulePublication $publication, DateTimeInterface $now): self
    {
        return match (true) {
            $publication === null => self::Draft,
            $publication->revoked_at !== null => self::Revoked,
            $publication->opens_at > $now => self::Waiting,
            default => self::Open,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Черновик',
            self::Waiting => 'Ожидает открытия',
            self::Open => 'Открыта',
            self::Revoked => 'Ссылка отозвана',
        };
    }
}
