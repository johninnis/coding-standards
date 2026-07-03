<?php

declare(strict_types=1);

namespace Acme\Vo\Domain\ValueObject;

final class Uuid
{
    private function __construct(private string $value)
    {
    }

    public static function of(string $value): self
    {
        return new self($value);
    }
}

final class Cache
{
    private static array $entries = [];

    public static function remember(string $key): string
    {
        return self::$entries[$key] ??= $key;
    }
}
