<?php

declare(strict_types=1);

namespace Acme\Vo\Domain\ValueObject;

final class PublicKey
{
    public function __construct(public string $hex)
    {
    }

    public function equals(object $other): bool
    {
        return $other instanceof self && $other->hex === $this->hex;
    }

    public function isEqualTo($other): bool
    {
        return $other === $this;
    }
}
