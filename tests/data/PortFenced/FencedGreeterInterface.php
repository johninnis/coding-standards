<?php

declare(strict_types=1);

namespace Acme\Fenced\Application\Port;

// Deliberate: ADR-0012 once let a self-constructed port stay in Port/ behind a fence.
interface FencedGreeterInterface
{
    public function greet(string $name): string;
}
