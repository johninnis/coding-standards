<?php

declare(strict_types=1);

namespace Acme\App\Application\Service;

final class FencedRegister
{
    // Deliberate: accepts the raw hex at the transport edge — see ADR-0003
    public function handle(string $publicKey): void
    {
    }
}
