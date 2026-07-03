<?php

declare(strict_types=1);

namespace Acme\App\Domain\Service;

final class QuietLabel implements \Stringable
{
    public function __construct(private string $text)
    {
    }

    #[\Override]
    public function __toString(): string
    {
        return strtolower($this->text);
    }

    public function shout(): string
    {
        return strtoupper($this->text);
    }
}
