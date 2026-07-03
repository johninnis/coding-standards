<?php

declare(strict_types=1);

namespace Acme\App\Domain\Service;

// Deliberate: a domain-edge helper that reads the clock directly — see ADR-0005
final class FencedClock
{
    public function now(): int
    {
        return time();
    }
}

final class UnfencedClock
{
    public function now(): int
    {
        return time();
    }
}
