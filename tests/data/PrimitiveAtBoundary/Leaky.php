<?php

declare(strict_types=1);

namespace Acme\App\Application\Service;

final class Register
{
    public string $publicKey;

    public function handle(string $publicKey, int $count): void
    {
    }
}
