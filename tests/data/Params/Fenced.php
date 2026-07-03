<?php

declare(strict_types=1);

namespace Acme\App\Application\UseCase;

final class Wired
{
    // Deliberate: irreducible protocol shape — see ADR-0001
    public function fenced(string $a, string $b, string $c, string $d): void
    {
    }

    public function unfenced(string $a, string $b, string $c, string $d): void
    {
    }
}
