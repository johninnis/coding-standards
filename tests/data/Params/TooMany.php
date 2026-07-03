<?php

declare(strict_types=1);

namespace Acme\App\Application\UseCase;

function build(int $a, int $b, int $c, int $d): int
{
    return $a + $b + $c + $d;
}

final class RegisterUser
{
    public function handle(string $a, string $b, string $c, string $d): void
    {
    }

    public function ok(string $a, string $b, string $c): void
    {
    }
}
