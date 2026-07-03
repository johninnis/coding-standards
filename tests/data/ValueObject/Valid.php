<?php

declare(strict_types=1);

namespace Acme\Vo\Domain\ValueObject;

final readonly class Price
{
    public function __construct(public int $cents)
    {
    }
}
