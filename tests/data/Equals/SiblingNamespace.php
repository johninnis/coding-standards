<?php

declare(strict_types=1);

namespace Acme\Vo\Domain\ValueObjectSupport;

final class Comparator
{
    public function equals(object $other): bool
    {
        return $other === $this;
    }
}
