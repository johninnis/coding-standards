<?php

declare(strict_types=1);

namespace Acme\App\Domain\ValueObject;

final readonly class PublicKey
{
    public function __construct(public string $hex)
    {
    }
}
