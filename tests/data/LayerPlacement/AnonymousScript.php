<?php

declare(strict_types=1);

use Acme\Kernel\Application\Port\LifecycleInterface;

$lifecycle = new class implements LifecycleInterface {
    public function start(): void
    {
    }
};
