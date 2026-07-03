<?php

declare(strict_types=1);

namespace Acme\App\Domain\Service;

final class LoudLabel implements \Stringable
{
    public function __construct(private string $text)
    {
    }

    public function __toString(): string
    {
        return strtoupper($this->text);
    }
}
