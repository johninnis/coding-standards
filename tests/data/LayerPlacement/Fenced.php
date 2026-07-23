<?php

declare(strict_types=1);

namespace Acme\Signer;

use Acme\Kernel\Application\Port\LifecycleInterface;

// Deliberate: ADR-0007 keeps this at the root while the split lands.
final class FencedLifecycle implements LifecycleInterface
{
}
