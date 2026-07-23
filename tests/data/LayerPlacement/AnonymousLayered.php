<?php

declare(strict_types=1);

namespace Acme\Signer\Infrastructure\Runtime;

use Acme\Kernel\Application\Port\LifecycleInterface;

final class AnonymousLayeredHost
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
