<?php

declare(strict_types=1);

namespace Acme\App\Domain\Service;

final class EventSerializer
{
    public const int MAX_COLOR = 1;

    public function normalize(
        string $color,
    ): string {
        return $color;
    }
}
