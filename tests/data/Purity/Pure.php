<?php

declare(strict_types=1);

namespace Acme\App\Domain\Service;

final class PureCalculator
{
    public function classify(int $amount): string
    {
        return match (true) {
            $amount < 0 => 'debit',
            $amount > 0 => 'credit',
            default => 'zero',
        };
    }
}
