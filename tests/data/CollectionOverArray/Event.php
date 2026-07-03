<?php

declare(strict_types=1);

namespace Acme\App\Domain\ValueObject;

final readonly class Event
{
    public function __construct(public string $id)
    {
    }
}
