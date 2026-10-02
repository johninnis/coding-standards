<?php

declare(strict_types=1);

namespace Acme\App\Infrastructure\Crypto;

final readonly class Wrapper
{
    public function __construct(private string $a, private string $b, private string $c, private string $d)
    {
    }
}
