<?php

declare(strict_types=1);

namespace Acme\Greetings\Application\Service;

use Acme\Greetings\Application\Port\GreeterInterface;

final class Greeter implements GreeterInterface
{
    public function greet(string $name): string
    {
        return "Hello, {$name}";
    }
}
