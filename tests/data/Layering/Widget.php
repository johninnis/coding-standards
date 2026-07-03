<?php

declare(strict_types=1);

namespace Shop\Catalogue\Domain\Model;

use Acme\Toolkit\Domain\Price;
use Acme\Toolkit\Infrastructure\Persistence\Store;
use Psr\Log\LoggerInterface;

final class Widget
{
    public function __construct(
        private Price $price,
        private Store $store,
        private LoggerInterface $logger,
    ) {
    }
}
