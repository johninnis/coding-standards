<?php

declare(strict_types=1);

namespace Acme\App\Domain\Entity;

final readonly class Order
{
    public function __construct(public string $id, public string $customer, public int $total, public string $currency)
    {
    }
}
