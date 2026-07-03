<?php

declare(strict_types=1);

namespace Acme\App\Infrastructure\Time;

final class SystemClock
{
    public function now(): int
    {
        return time();
    }
}
