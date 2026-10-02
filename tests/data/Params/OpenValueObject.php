<?php

declare(strict_types=1);

namespace Acme\App\Domain\ValueObject;

readonly class Range
{
    public function __construct(public int $a, public int $b, public int $c, public int $d)
    {
    }
}
