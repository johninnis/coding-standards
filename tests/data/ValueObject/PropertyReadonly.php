<?php

declare(strict_types=1);

namespace Acme\Vo\Domain\ValueObject;

final class Token
{
    public function __construct(public readonly string $value)
    {
    }
}
