<?php

declare(strict_types=1);

namespace Acme\Greetings\Application\Port;

interface GreeterInterface
{
    public function greet(string $name): string;
}
