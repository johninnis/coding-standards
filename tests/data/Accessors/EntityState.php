<?php

declare(strict_types=1);

namespace Acme\App\Domain\Entity;

final class Order
{
    public private(set) string $status;

    public function __construct(string $status)
    {
        $this->status = $status;
    }
}

final class Account
{
    public function __construct(public private(set) string $owner)
    {
    }
}
