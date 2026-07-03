<?php

declare(strict_types=1);

namespace Acme\Vo\Domain\ValueObject;

final readonly class Money
{
    public function __construct(public int $cents)
    {
    }

    public function getCents(): int
    {
        return $this->cents;
    }
}
