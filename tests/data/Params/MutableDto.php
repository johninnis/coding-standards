<?php

declare(strict_types=1);

namespace Acme\App\Application\DTO;

final class Draft
{
    public function __construct(public string $a, public string $b, public string $c, public string $d)
    {
    }
}
