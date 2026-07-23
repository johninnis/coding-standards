<?php

declare(strict_types=1);

namespace Acme\Signer\Tests\Unit\Application;

use Acme\Kernel\Application\Port\LifecycleInterface;

final class AnonymousInTestCase
{
    public function double(): LifecycleInterface
    {
        return new class implements LifecycleInterface {
            public function start(): void
            {
            }
        };
    }
}
