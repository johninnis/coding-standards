<?php

declare(strict_types=1);

namespace Acme\App\Domain\Service;

final class Timers
{
    // Deliberate: edge helper reads the clock directly — see ADR-0005
    public function fenced(): int
    {
        return time();
    }

    public function unfenced(): int
    {
        return time();
    }
}
