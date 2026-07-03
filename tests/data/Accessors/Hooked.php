<?php

declare(strict_types=1);

namespace Acme\Vo\Domain\ValueObject;

final class Temperature
{
    public string $label {
        get => 'x';
    }

    public private(set) int $celsius;

    public function __construct(int $celsius)
    {
        $this->celsius = $celsius;
    }
}
