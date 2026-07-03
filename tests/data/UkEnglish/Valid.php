<?php

declare(strict_types=1);

namespace Acme\App\Domain\Service;

final class EventSerialiser implements \JsonSerializable
{
    public const string ORGANISATION = 'x';

    #[\Override]
    public function jsonSerialize(): string
    {
        return 'x';
    }

    public function normalise(string $colour, int $diameter): string
    {
        return $colour.$diameter;
    }
}
