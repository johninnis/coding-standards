<?php

declare(strict_types=1);

namespace Acme\Domain\Model;

use Innis\Other\Domain\Money;
use Innis\Other\Infrastructure\Crypto\Hasher;

final class Account
{
    public function __construct(
        private Money $money,
        private Hasher $hasher,
    ) {
    }
}
