<?php

declare(strict_types=1);

namespace Acme\Signer;

use Acme\Kernel\Application\Port\LifecycleInterface;

final class AnonymousUnlayeredHost
{
    public function lifecycle(): LifecycleInterface
    {
        return new class implements LifecycleInterface {
            public function start(): void
            {
            }
        };
    }
}
