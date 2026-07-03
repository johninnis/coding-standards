<?php

declare(strict_types=1);

namespace Acme\App\Domain\ValueObject;

final readonly class Message
{
    public function __construct(public string $type)
    {
    }
}
