<?php

declare(strict_types=1);

namespace Acme\App\Tests\Fake;

use Innis\CodingStandards\Tests\Support\GreetingInterface;

final class MissingOnFirstParty implements GreetingInterface
{
    public function greet(): string
    {
        return 'hi';
    }
}

final class MissingOnBuiltin implements \Stringable
{
    public function __toString(): string
    {
        return 'LOUD';
    }
}

final class Attributed implements GreetingInterface
{
    #[\Override]
    public function greet(): string
    {
        return 'hey';
    }
}
