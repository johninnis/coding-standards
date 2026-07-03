<?php

declare(strict_types=1);

namespace Acme\Vo\Domain\ValueObject;

final readonly class Amount
{
    public function __construct(public int $cents)
    {
    }

    public static function tryFromString(string $raw): ?self
    {
        return is_numeric($raw) ? new self((int) $raw) : null;
    }

    public static function fromCents(int $cents): self
    {
        if ($cents < 0) {
            throw new \InvalidArgumentException('negative');
        }

        return new self($cents);
    }

    public static function of(int $cents): self
    {
        return new self($cents);
    }
}

final readonly class Token
{
    public function __construct(public string $value)
    {
    }

    // Deliberate: translates a thrown library fault into a value at this boundary; ADR-0002
    public static function tryFromRaw(string $raw): self
    {
        return new self($raw);
    }
}
