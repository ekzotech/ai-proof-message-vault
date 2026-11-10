<?php

namespace App\Support;

use DateTimeImmutable;
use DateTimeZone;

class LocalOpeningTime
{
    /** @return array<string, DateTimeImmutable> */
    public static function candidates(string $local, string $timezone): array
    {
        $wall = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $local, new DateTimeZone('UTC'));

        if ($wall === false || $wall->format('Y-m-d\TH:i') !== $local) {
            return [];
        }

        $zone = new DateTimeZone($timezone);
        $timestamp = $wall->getTimestamp();
        $transitions = $zone->getTransitions($timestamp - 172800, $timestamp + 172800);
        $offsets = array_unique(array_column($transitions, 'offset'));
        $candidates = [];

        foreach ($offsets as $offset) {
            $utc = $wall->setTimestamp($timestamp - $offset);
            $inZone = $utc->setTimezone($zone);

            if ($inZone->format('Y-m-d\TH:i') === $local) {
                $candidates[$inZone->format('P')] = $utc;
            }
        }

        return $candidates;
    }
}
