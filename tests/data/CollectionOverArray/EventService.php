<?php

declare(strict_types=1);

namespace Acme\App\Application\Service;

use Acme\App\Domain\ValueObject\Event;

final class EventService
{
    /**
     * @param list<Event> $events
     *
     * @return list<Event>
     */
    public function process(
        array $events,
    ): array {
        return $events;
    }
}
