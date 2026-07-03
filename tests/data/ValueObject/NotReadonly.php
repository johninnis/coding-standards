<?php

declare(strict_types=1);

namespace Acme\Vo\Domain\ValueObject;

final class Money
{
    public function __construct(public int $amount)
    {
    }
}
