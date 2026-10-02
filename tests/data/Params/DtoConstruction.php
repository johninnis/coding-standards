<?php

declare(strict_types=1);

namespace Acme\App\Application\DTO;

final readonly class OrderSummary
{
    public function __construct(public string $id, public string $customer, public int $total, public string $currency)
    {
    }

    public static function from(string $id, string $customer, int $total, string $currency): self
    {
        return new self($id, $customer, $total, $currency);
    }

    public function describe(string $a, string $b, string $c, string $d): string
    {
        return $a.$b.$c.$d;
    }
}
