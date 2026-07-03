<?php

declare(strict_types=1);

namespace Acme\Vo\Domain\ValueObject;

final class Money
{
    public function __construct(public int $cents)
    {
    }

    public function equals(self $other): bool
    {
        return $other->cents === $this->cents;
    }

    public function withCents(int $cents): self
    {
        return new self($cents);
    }
}
