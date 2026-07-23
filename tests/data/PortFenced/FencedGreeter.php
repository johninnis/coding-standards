<?php

declare(strict_types=1);

namespace Acme\Fenced\Application\Service;

use Acme\Fenced\Application\Port\FencedGreeterInterface;

final class FencedGreeter implements FencedGreeterInterface
{
    public function greet(string $name): string
    {
        return "Hello, {$name}";
    }
}
