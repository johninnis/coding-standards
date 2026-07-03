<?php

declare(strict_types=1);

namespace Acme\Vo\Domain\ValueObject;

// Deliberate: property-level readonly to allow memory zeroing, see ADR-0007.
final class Secret
{
    public function __construct(public readonly string $value)
    {
    }
}

abstract class Shape
{
}
