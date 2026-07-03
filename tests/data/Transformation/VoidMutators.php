<?php

declare(strict_types=1);

namespace Acme\Vo\Domain\ValueObject;

final class Basket
{
    public function __construct(public int $count)
    {
    }

    public function withCount(int $count): void
    {
        $this->count = $count;
    }

    public function addItem(): bool
    {
        return true;
    }

    public function total(): int
    {
        return $this->count;
    }
}
