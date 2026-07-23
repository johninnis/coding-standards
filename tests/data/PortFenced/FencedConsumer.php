<?php

declare(strict_types=1);

namespace Acme\Fenced\Application\Service;

final class FencedConsumer
{
    public function make(): FencedGreeter
    {
        return new FencedGreeter();
    }
}
