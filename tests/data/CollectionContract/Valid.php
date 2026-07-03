<?php

declare(strict_types=1);

namespace Acme\App\Domain\Collection;

final class TagCollection implements \IteratorAggregate, \Countable
{
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator([]);
    }

    public function count(): int
    {
        return 0;
    }
}
