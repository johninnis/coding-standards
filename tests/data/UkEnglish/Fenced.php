<?php

declare(strict_types=1);

namespace Acme\App\Domain\Service;

final class Palette
{
    // Deliberate: matches the external SDK's method spelling — see ADR-0001
    public function normalize(string $color): string
    {
        return $color;
    }

    public function render(string $color): string
    {
        return $color;
    }
}
