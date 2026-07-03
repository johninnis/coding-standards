<?php

declare(strict_types=1);

namespace Acme\App\Domain\Collection;

use Acme\App\Domain\ValueObject\Event;

final class EventCollection
{
    /**
     * @param list<Event> $events
     */
    public function __construct(private array $events)
    {
    }
}
