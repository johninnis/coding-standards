<?php

declare(strict_types=1);

namespace Acme\App\Application\Service;

use Acme\App\Domain\Collection\EventCollection;

final class TagName
{
    public function __construct(public string $value)
    {
    }
}

final class CleanService
{
    /**
     * @param list<TagName> $tags
     */
    public function label(array $tags): EventCollection
    {
        return new EventCollection([]);
    }
}
