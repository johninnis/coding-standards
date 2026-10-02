<?php

declare(strict_types=1);

namespace Acme\App\Domain\ValueObject;

final readonly class Address
{
    public function __construct(public string $street, public string $town, public string $region, public string $postcode)
    {
    }

    public static function from(string $street, string $town, string $region, string $postcode): self
    {
        return new self($street, $town, $region, $postcode);
    }

    public static function tryFrom(string $street, string $town, string $region, string $postcode): ?self
    {
        return new self($street, $town, $region, $postcode);
    }

    public static function make(string $street, string $town, string $region, string $postcode): static
    {
        return new static($street, $town, $region, $postcode);
    }

    public function withLines(string $street, string $town, string $region, string $postcode): self
    {
        return new self($street, $town, $region, $postcode);
    }

    public static function format(string $street, string $town, string $region, string $postcode): string
    {
        return $street.$town.$region.$postcode;
    }
}
