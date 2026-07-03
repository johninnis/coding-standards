<?php

declare(strict_types=1);

namespace Acme\Greetings\Application\Service;

final class Consumer
{
    public function make(): Greeter
    {
        return new Greeter();
    }
}
