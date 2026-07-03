<?php

declare(strict_types=1);

namespace Acme\App\Domain\Service;

final class Farewell
{
    public function farewell(): string
    {
        return 'goodbye';
    }
}
