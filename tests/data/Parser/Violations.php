<?php

declare(strict_types=1);

namespace Acme\Vo\Domain\ValueObject;

final readonly class PublicKey
{
    public function __construct(public string $hex)
    {
    }

    public static function tryFromHex(string $hex): self
    {
        if (!ctype_xdigit($hex)) {
            throw new \InvalidArgumentException('bad');
        }

        return new self($hex);
    }

    public static function fromRaw(string $raw): ?self
    {
        return ctype_xdigit($raw) ? new self($raw) : null;
    }
}
