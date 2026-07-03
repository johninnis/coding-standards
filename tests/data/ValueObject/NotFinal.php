<?php

declare(strict_types=1);

namespace Acme\Vo\Domain\ValueObject;

class Address
{
    public function __construct(public string $city)
    {
    }
}
